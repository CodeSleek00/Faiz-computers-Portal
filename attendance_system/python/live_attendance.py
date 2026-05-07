import cv2
import face_recognition
import pickle
import requests
from datetime import datetime
with open("../trainer/encodings.pkl", "rb") as f:
    data = pickle.load(f)

video = cv2.VideoCapture(0)

marked_students = []

while True:

    ret, frame = video.read()

    small_frame = cv2.resize(frame, (0,0), fx=0.25, fy=0.25)

    rgb = cv2.cvtColor(small_frame, cv2.COLOR_BGR2RGB)

    locations = face_recognition.face_locations(rgb)

    encodings = face_recognition.face_encodings(rgb, locations)

    for encoding, location in zip(encodings, locations):

        matches = face_recognition.compare_faces(
            data["encodings"],
            encoding
        )

        name = "Unknown"

        if True in matches:

            matched_index = matches.index(True)

            name = data["names"][matched_index]

            if name not in marked_students:

                marked_students.append(name)

                parts = name.split("_")

                student_id = parts[0]
                table_name = parts[1]

                requests.post(
                    "https://yourdomain.com/api/attendance_api.php",
                    data={
                        "student_id": student_id,
                        "table_name": table_name
                    }
                )

        top, right, bottom, left = location

        top *= 4
        right *= 4
        bottom *= 4
        left *= 4

        cv2.rectangle(frame,
        (left, top),
        (right, bottom),
        (0,255,0),
        2)

        cv2.putText(frame,
        name,
        (left, top - 10),
        cv2.FONT_HERSHEY_SIMPLEX,
        0.8,
        (0,255,0),
        2)

    cv2.imshow("Attendance AI", frame)

    if cv2.waitKey(1) == ord('q'):
        break

video.release()
cv2.destroyAllWindows()