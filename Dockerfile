# Default Dockerfile
#
# @link     https://www.hyperf.io
# @document https://hyperf.wiki
# @contact  group@hyperf.io
# @license  https://github.com/hyperf/hyperf/blob/master/LICENSE

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole

RUN apk update

RUN apk add python3 && /usr/bin/python3.8 -m pip install --upgrade pip &&  pip3 install edge-tts



EXPOSE 9501

