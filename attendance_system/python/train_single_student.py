import face_recognition
import pickle
import os
import sys

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

if os.path.exists(encodings_file):

    with open(encodings_file, "rb") as f:
        data = pickle.load(f)

        known_encodings = data["encodings"]
        known_names = data["names"]

for image_name in os.listdir(folder):

    image_path = os.path.join(folder, image_name)

    image = face_recognition.load_image_file(image_path)

    encodings = face_recognition.face_encodings(image)

    if len(encodings) > 0:

        encoding = encodings[0]

        known_encodings.append(encoding)
        known_names.append(f"{student_id}_{table_name}")

save_data = {
    "encodings": known_encodings,
    "names": known_names
}

with open(encodings_file, "wb") as f:
    pickle.dump(save_data, f)

print("Student Training Complete")
