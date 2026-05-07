import pickle
import os
import sys
import dlib
import numpy as np

from models import ensure_models

student_id = sys.argv[1]
table_name = sys.argv[2]

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
APP_DIR = os.path.abspath(os.path.join(BASE_DIR, ".."))

folder = os.path.join(APP_DIR, "dataset", f"{student_id}_{table_name}")

encodings_file = os.path.join(APP_DIR, "trainer", "encodings.pkl")

known_encodings = []
known_names = []

if not os.path.isdir(folder):
    raise SystemExit(f"Dataset folder not found: {folder}")

os.makedirs(os.path.dirname(encodings_file), exist_ok=True)

models = ensure_models()
detector = dlib.get_frontal_face_detector()
sp = dlib.shape_predictor(models["shape5"])
facerec = dlib.face_recognition_model_v1(models["resnet"])

if os.path.exists(encodings_file):

    with open(encodings_file, "rb") as f:
        data = pickle.load(f)

        known_encodings = data["encodings"]
        known_names = data["names"]

for image_name in os.listdir(folder):

    image_path = os.path.join(folder, image_name)

    try:
        img_rgb = dlib.load_rgb_image(image_path)
    except Exception:
        continue
    dets = detector(img_rgb, 1)
    if len(dets) == 0:
        continue

    # Use first face
    shape = sp(img_rgb, dets[0])
    face_descriptor = facerec.compute_face_descriptor(img_rgb, shape)
    encoding = np.array(face_descriptor, dtype=np.float32)

    known_encodings.append(encoding)
    known_names.append(f"{student_id}_{table_name}")

save_data = {
    "encodings": known_encodings,
    "names": known_names
}

with open(encodings_file, "wb") as f:
    pickle.dump(save_data, f)

print("Student Training Complete")
