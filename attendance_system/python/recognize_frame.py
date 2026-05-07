import os
import sys
import pickle


def main() -> int:
    if len(sys.argv) < 2:
        print("ERR missing_image_path")
        return 2

    image_path = sys.argv[1]

    base_dir = os.path.dirname(os.path.abspath(__file__))
    app_dir = os.path.abspath(os.path.join(base_dir, ".."))
    encodings_path = os.path.join(app_dir, "trainer", "encodings.pkl")

    if not os.path.exists(encodings_path):
        print("ERR encodings_missing")
        return 3

    with open(encodings_path, "rb") as f:
        data = pickle.load(f)

    try:
        import face_recognition  # type: ignore
    except Exception as e:
        print(f"ERR face_recognition_import {e}")
        return 5

    image = face_recognition.load_image_file(image_path)
    encodings = face_recognition.face_encodings(image)
    if len(encodings) == 0:
        print("NOFACE")
        return 0

    encoding = encodings[0]
    matches = face_recognition.compare_faces(data.get("encodings", []), encoding)
    if True not in matches:
        print("UNKNOWN")
        return 0

    idx = matches.index(True)
    name = data.get("names", [])[idx]

    # Expected format: "<student_id>_<table_name>"
    parts = str(name).split("_", 1)
    if len(parts) != 2:
        print("ERR bad_encoding_name")
        return 4

    student_id, table_name = parts[0], parts[1]
    print(f"OK {student_id} {table_name}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
