FROM node:20-bookworm-slim

WORKDIR /var/www/html

COPY docker/node/start.sh /usr/local/bin/libra-shell-node-start

RUN chmod +x /usr/local/bin/libra-shell-node-start

CMD ["libra-shell-node-start"]
