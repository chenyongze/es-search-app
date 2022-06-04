FROM php:8.0-rc-cli-alpine

ENV TZ Asia/Shanghai
ENV CONTAINER_PACKAGE_URL mirrors.aliyun.com
#资源替换 国内
RUN sed -i "s/dl-cdn.alpinelinux.org/${CONTAINER_PACKAGE_URL}/g" /etc/apk/repositories

WORKDIR /app