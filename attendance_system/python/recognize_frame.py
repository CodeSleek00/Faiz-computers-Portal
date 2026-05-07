import os
import sys
import pickle
import dlib
import numpy as np


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

    # Load models locally (auto-download if missing)
    base_dir = os.path.dirname(os.path.abspath(__file__))
    sys.path.insert(0, base_dir)
    from models import ensure_models  # noqa

    models = ensure_models()
    detector = dlib.get_frontal_face_detector()
    sp = dlib.shape_predictor(models["shape5"])
    facerec = dlib.face_recognition_model_v1(models["resnet"])

    try:
        img_rgb = dlib.load_rgb_image(image_path)
    except Exception:
        print("ERR bad_image")
        return 6
    dets = detector(img_rgb, 1)
    if len(dets) == 0:
        print("NOFACE")
        return 0

    shape = sp(img_rgb, dets[0])
    face_descriptor = facerec.compute_face_descriptor(img_rgb, shape)
    encoding = np.array(face_descriptor, dtype=np.float32)

    known_encodings = data.get("encodings", [])
    known_names = data.get("names", [])
    if not known_encodings or not known_names:
        print("ERR encodings_empty")
        return 7

    # Compute L2 distance to known encodings
    enc_matrix = np.asarray(known_encodings, dtype=np.float32)
    dists = np.linalg.norm(enc_matrix - encoding, axis=1)
    best_idx = int(np.argmin(dists))
    best_dist = float(dists[best_idx])

    # Threshold similar to common face_recognition usage
    if best_dist > 0.6:
        print("UNKNOWN")
        return 0

    name = known_names[best_idx]

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
