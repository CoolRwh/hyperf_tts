# Default Dockerfile
#
# @link     https://www.hyperf.io
# @document https://hyperf.wiki
# @contact  group@hyperf.io
# @license  https://github.com/hyperf/hyperf/blob/master/LICENSE

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole

RUN apk update

RUN apk add python3 && /usr/bin/python3.8 -m pip install --upgrade pip &&  pip3 install edge-tts


# Composer Cache
# COPY ./composer.* /opt/www/
# RUN composer install --no-dev --no-scripts

COPY . /docker/wwwroot/hyperf_tts

RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer

RUN composer install --no-dev -o

EXPOSE 9501

