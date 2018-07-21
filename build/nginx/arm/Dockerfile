FROM resin/rpi-raspbian:stretch as builder

FROM arm32v7/nginx:stable

COPY --from=builder /usr/bin/qemu-arm-static /usr/bin/qemu-arm-static

COPY build/nginx/nginx.conf /etc/nginx/