import pickle
import os
import cv2
import dlib
import numpy as np

from models import ensure_models

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
APP_DIR = os.path.abspath(os.path.join(BASE_DIR, ".."))

dataset_path = os.path.join(APP_DIR, "dataset")

known_encodings = []
known_names = []

if not os.path.isdir(dataset_path):
    raise SystemExit(f"Dataset folder not found: {dataset_path}")

models = ensure_models()
detector = dlib.get_frontal_face_detector()
sp = dlib.shape_predictor(models["shape5"])
facerec = dlib.face_recognition_model_v1(models["resnet"])

for folder in os.listdir(dataset_path):

    folder_path = os.path.join(dataset_path, folder)

    if os.path.isdir(folder_path):

        for image_name in os.listdir(folder_path):

            image_path = os.path.join(folder_path, image_name)

            img_bgr = cv2.imread(image_path)
            if img_bgr is None:
                continue

            img_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)
            dets = detector(img_rgb, 1)
            if len(dets) == 0:
                continue

            shape = sp(img_rgb, dets[0])
            face_descriptor = facerec.compute_face_descriptor(img_rgb, shape)
            encoding = np.array(face_descriptor, dtype=np.float32)

            known_encodings.append(encoding)
            known_names.append(folder)

save_data = {
    "encodings": known_encodings,
    "names": known_names
}

trainer_dir = os.path.join(APP_DIR, "trainer")
os.makedirs(trainer_dir, exist_ok=True)

with open(os.path.join(trainer_dir, "encodings.pkl"), "wb") as f:
    pickle.dump(save_data, f)

print("All Students Trained Successfully")
