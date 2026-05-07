import express from "express";
import dotenv from "dotenv";
import mysql from "mysql2/promise";

dotenv.config();

const PORT = Number(process.env.PORT || 3000);

const DB_HOST = process.env.DB_HOST;
const DB_USER = process.env.DB_USER;
const DB_PASS = process.env.DB_PASS;
const DB_NAME = process.env.DB_NAME;

function bad(res, msg) {
  res.status(400).json({ ok: false, error: msg });
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
      student_id VARCHAR(64) NOT NULL,
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

function normalizeEmbedding(raw) {
  if (!Array.isArray(raw)) return null;
  if (raw.length < 64) return null;
  const out = raw.map((v) => Number(v));
  if (out.some((n) => Number.isNaN(n))) return null;
  return out;
}

const app = express();
app.use(express.json({ limit: "2mb" }));

let dbPool = null;

app.get("/health", (_req, res) => res.json({ ok: true }));

// Enrollment: expects embedding computed in browser (face-api.js)
app.post("/api/enroll", async (req, res) => {
  try {
    const student_id = String(req.body?.student_id || "").replace(/[^a-zA-Z0-9_]/g, "");
    const table_name = String(req.body?.table_name || "").replace(/[^a-zA-Z0-9_]/g, "");
    const embedding = normalizeEmbedding(req.body?.embedding);

    if (!student_id || !table_name) return bad(res, "Missing student_id/table_name");
    if (!embedding) return bad(res, "Missing/invalid embedding");

    await dbPool.query(
      "INSERT INTO face_embeddings (student_id, table_name, embedding) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE embedding=VALUES(embedding)",
      [student_id, table_name, JSON.stringify(embedding)]
    );

    res.json({ ok: true, enrolled: true });
  } catch (e) {
    res.status(500).json({ ok: false, error: "enroll_failed", detail: String(e?.message || e) });
  }
});

// Recognition: expects embedding computed in browser
app.post("/api/recognize", async (req, res) => {
  try {
    const embedding = normalizeEmbedding(req.body?.embedding);
    if (!embedding) return bad(res, "Missing/invalid embedding");

    const [rows] = await dbPool.query("SELECT student_id, table_name, embedding FROM face_embeddings");

    let best = null;
    for (const row of rows) {
      const known = normalizeEmbedding(JSON.parse(row.embedding));
      if (!known) continue;
      const dist = l2(known, embedding);
      if (!best || dist < best.dist) best = { student_id: row.student_id, table_name: row.table_name, dist };
    }

    if (!best) {
      res.json({ ok: true, recognized: false, reason: "no_enrollments" });
      return;
    }

    // Threshold; tune if needed.
    if (best.dist > 0.6) {
      res.json({ ok: true, recognized: false, reason: "unknown", best_dist: best.dist });
      return;
    }

    res.json({ ok: true, recognized: true, student_id: String(best.student_id), table_name: best.table_name, dist: best.dist });
  } catch (e) {
    res.status(500).json({ ok: false, error: "recognize_failed", detail: String(e?.message || e) });
  }
});

async function main() {
  dbPool = await getDb();
  await ensureSchema(dbPool);
  app.listen(PORT, () => console.log(`Embedding API listening on ${PORT}`));
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
