import fs from "node:fs";
import path from "node:path";
import crypto from "node:crypto";
import https from "node:https";
import http from "node:http";

import express from "express";
import dotenv from "dotenv";
import mysql from "mysql2/promise";

import * as faceapi from "@vladmandic/face-api";
import canvas from "canvas";

dotenv.config();

const { Canvas, Image, ImageData } = canvas;
faceapi.env.monkeyPatch({ Canvas, Image, ImageData });

const PORT = Number(process.env.PORT || 3000);

const DB_HOST = process.env.DB_HOST;
const DB_USER = process.env.DB_USER;
const DB_PASS = process.env.DB_PASS;
const DB_NAME = process.env.DB_NAME;

const MODEL_DIR = path.resolve(process.cwd(), "models");
const MODELS_BASE_URL =
  process.env.MODELS_BASE_URL ||
  "https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights";

function downloadToFile(url, destPath) {
  return new Promise((resolve, reject) => {
    const proto = url.startsWith("https:") ? https : http;
    fs.mkdirSync(path.dirname(destPath), { recursive: true });
    const file = fs.createWriteStream(destPath);
    proto
      .get(url, (res) => {
        if (res.statusCode && res.statusCode >= 400) {
          reject(new Error(`HTTP ${res.statusCode} for ${url}`));
          return;
        }
        res.pipe(file);
        file.on("finish", () => file.close(resolve));
      })
      .on("error", (err) => {
        try {
          fs.unlinkSync(destPath);
        } catch {}
        reject(err);
      });
  });
}

async function ensureModels() {
  const required = [
    "ssd_mobilenetv1_model-weights_manifest.json",
    "ssd_mobilenetv1_model-shard1",
    "face_landmark_68_model-weights_manifest.json",
    "face_landmark_68_model-shard1",
    "face_recognition_model-weights_manifest.json",
    "face_recognition_model-shard1",
  ];

  for (const file of required) {
    const local = path.join(MODEL_DIR, file);
    if (!fs.existsSync(local)) {
      const url = `${MODELS_BASE_URL}/${file}`;
      await downloadToFile(url, local);
    }
  }

  await faceapi.nets.ssdMobilenetv1.loadFromDisk(MODEL_DIR);
  await faceapi.nets.faceLandmark68Net.loadFromDisk(MODEL_DIR);
  await faceapi.nets.faceRecognitionNet.loadFromDisk(MODEL_DIR);
}

function decodeDataUrl(dataUrl) {
  const m = /^data:image\/\w+;base64,(.+)$/i.exec(dataUrl || "");
  if (!m) return null;
  return Buffer.from(m[1], "base64");
}

async function bufferToImage(buf) {
  return await canvas.loadImage(buf);
}

async function getDb() {
  if (!DB_HOST || !DB_USER || !DB_NAME) {
    throw new Error("Missing DB env vars (DB_HOST, DB_USER, DB_NAME)");
  }
  return mysql.createPool({
    host: DB_HOST,
    user: DB_USER,
    password: DB_PASS || "",
    database: DB_NAME,
    waitForConnections: true,
    connectionLimit: 5,
  });
}

async function ensureSchema(db) {
  await db.query(`
    CREATE TABLE IF NOT EXISTS face_embeddings (
      id INT AUTO_INCREMENT PRIMARY KEY,
      student_id BIGINT NOT NULL,
      table_name VARCHAR(64) NOT NULL,
      embedding JSON NOT NULL,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY uniq_student_table (student_id, table_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  `);
}

function l2(a, b) {
  let sum = 0;
  for (let i = 0; i < a.length; i++) {
    const d = a[i] - b[i];
    sum += d * d;
  }
  return Math.sqrt(sum);
}

const app = express();
app.use(express.json({ limit: "10mb" }));

let dbPool = null;

app.get("/health", async (_req, res) => {
  res.json({ ok: true });
});

app.post("/api/enroll", async (req, res) => {
  try {
    const student_id = String(req.body?.student_id || "").replace(/\D/g, "");
    const table_name = String(req.body?.table_name || "").replace(/[^a-zA-Z0-9_]/g, "");
    const image = req.body?.image;

    if (!student_id || !table_name || !image) {
      res.status(400).json({ ok: false, error: "Missing student_id/table_name/image" });
      return;
    }

    const buf = decodeDataUrl(image);
    if (!buf) {
      res.status(400).json({ ok: false, error: "Invalid image dataURL" });
      return;
    }

    const img = await bufferToImage(buf);
    const detection = await faceapi
      .detectSingleFace(img)
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      res.json({ ok: true, enrolled: false, reason: "no_face" });
      return;
    }

    const embedding = Array.from(detection.descriptor);
    await dbPool.query(
      "INSERT INTO face_embeddings (student_id, table_name, embedding) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE embedding=VALUES(embedding)",
      [Number(student_id), table_name, JSON.stringify(embedding)]
    );

    res.json({ ok: true, enrolled: true });
  } catch (e) {
    res.status(500).json({ ok: false, error: "enroll_failed", detail: String(e?.message || e) });
  }
});

app.post("/api/recognize", async (req, res) => {
  try {
    const image = req.body?.image;
    const buf = decodeDataUrl(image);
    if (!buf) {
      res.status(400).json({ ok: false, error: "Invalid image dataURL" });
      return;
    }

    const img = await bufferToImage(buf);
    const detection = await faceapi
      .detectSingleFace(img)
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) {
      res.json({ ok: true, recognized: false, reason: "no_face" });
      return;
    }

    const embedding = Array.from(detection.descriptor);
    const [rows] = await dbPool.query("SELECT student_id, table_name, embedding FROM face_embeddings");
    let best = null;

    for (const row of rows) {
      const known = JSON.parse(row.embedding);
      const dist = l2(known, embedding);
      if (!best || dist < best.dist) {
        best = { student_id: row.student_id, table_name: row.table_name, dist };
      }
    }

    // Threshold; tune as needed.
    if (!best || best.dist > 0.6) {
      res.json({ ok: true, recognized: false, reason: "unknown", best_dist: best?.dist ?? null });
      return;
    }

    res.json({ ok: true, recognized: true, student_id: String(best.student_id), table_name: best.table_name, dist: best.dist });
  } catch (e) {
    res.status(500).json({ ok: false, error: "recognize_failed", detail: String(e?.message || e) });
  }
});

async function main() {
  await ensureModels();
  dbPool = await getDb();
  await ensureSchema(dbPool);
  app.listen(PORT, () => {
    // eslint-disable-next-line no-console
    console.log(`Face API listening on ${PORT}`);
  });
}

main().catch((e) => {
  // eslint-disable-next-line no-console
  console.error(e);
  process.exit(1);
});

