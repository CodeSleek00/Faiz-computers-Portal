import face_recognition
import pickle
import os

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
APP_DIR = os.path.abspath(os.path.join(BASE_DIR, ".."))

dataset_path = os.path.join(APP_DIR, "dataset")

known_encodings = []
known_names = []

if not os.path.isdir(dataset_path):
    raise SystemExit(f"Dataset folder not found: {dataset_path}")

for folder in os.listdir(dataset_path):

    folder_path = os.path.join(dataset_path, folder)

    if os.path.isdir(folder_path):

        for image_name in os.listdir(folder_path):

            image_path = os.path.join(folder_path, image_name)

            image = face_recognition.load_image_file(image_path)

            encodings = face_recognition.face_encodings(image)

            if len(encodings) > 0:

                encoding = encodings[0]

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
