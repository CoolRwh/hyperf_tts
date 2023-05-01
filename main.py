from flask import Flask, request
import edge_tts
import asyncio
import uuid
import datetime

app = Flask(__name__)

voice = 'zh-CN-XiaoxiaoNeural'
rate = '-4%'
volume = '+0%'


@app.route('/', methods=['get'])
def tts():
    text = request.values.get('text')
    print(text)
    if(text == None):
        return 'text is null !'


    filePath = './mp3/' + get_uuid() + '.mp3'
    printTime(1)
    asyncio.run(makeMp3(text, filePath))
    printTime(2)
    fileData = open(filePath, 'rb').read()
    printTime(3)
    # app是Falsk实例 Flask(__name__)
    response = app.make_response(fileData)
    response.headers["Content-Disposition"] = 'inline;filename=text2voicetest.mp3'
    response.headers["Content-Type"] = 'audio/mpeg'
    response.headers["Cache-Control"] = 'no-cache'
    return response


async def makeMp3(text, filePath):
    tts = edge_tts.Communicate(text=text, voice=voice, rate=rate, volume=volume)
    await  tts.save(filePath)

async def makeToMp3_v2(text, filePath):
    tts = edge_tts.Communicate(text=text, voice=voice, rate=rate, volume=volume)
    await tts.stream()



def printTime(f = ''):
    time =  datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S.%f')
    print(f,time)

def get_uuid():
    get_timestamp_uuid = uuid.uuid1()
    return str(get_timestamp_uuid)


if __name__ == '__main__':
    app.run(host="0.0.0.0", port=9501, debug=True)
