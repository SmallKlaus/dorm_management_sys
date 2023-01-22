import numpy
import face_recognition
import cv2
import os

#function that makes and saves encodings
def getEncodings(images):
    encodeList = []
    for img in images:
        img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        encode  = face_recognition.face_encodings(img)[0]
        encodeList.append(encode)
    print("Number of image encodings found: ", len(images))
    return encodeList

path = '../res/images'
path = os.path.join('res', 'images', 'general_faces')
images = []
membersNames = []
rawImagesList = os.listdir(path)
#importing images and filling lists
print("Importing pictures...")
for img in rawImagesList:
    currImage = cv2.imread(f'{path}/{img}')
    images.append(currImage)
    membersNames.append(os.path.splitext(img)[0])
#get encodings
print("Finding Encoding, this may take a while, please wait....")
imagesEncodeList = getEncodings(images)
print('Encoding complete')

#main program 

videoCap = cv2.VideoCapture(0)

while True:
    res, img = videoCap.read()
    imgS = cv2.resize(img, (0,0), None, 0.25, 0.25)
    imgS = cv2.cvtColor(imgS, cv2.COLOR_BGR2RGB)

    faces = face_recognition.face_locations(imgS)
    encodings = face_recognition.face_encodings(imgS, faces)

    for encoding, faceLoc in zip(encodings, faces):
        matches = face_recognition.compare_faces(imagesEncodeList, encoding)
        faceDis = face_recognition.face_distance(imagesEncodeList, encoding)
        matchIndex = numpy.argmin(faceDis)
        if matches[matchIndex] :
            id = membersNames[matchIndex]
            cv2.putText(img, id, (200, 100), None, 1, (0, 255, 0), 3)

    cv2.imshow('Security System', img)
    k = cv2.waitKey(30) & 0xff
    if k == 27: # press 'ESC' to quit
        break
videoCap.release()
cv2.destroyAllWindows()



