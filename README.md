# Mophpidy
Telegram bot for controlling Mopidy music server written in reactive PHP.

[![Build Status](https://travis-ci.org/421p/Mophpidy.svg?branch=master)](https://travis-ci.org/421p/Mophpidy)
[![Join the chat at https://gitter.im/Mophpidy/Lobby](https://badges.gitter.im/Mophpidy/Lobby.svg)](https://gitter.im/Mophpidy/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Features
* Browsing collection
* Volume control
* Playback control
* Search (currently only gmusic and soundcloud)
* Favorites (all playlists that match `/favou?rites/i` in playlist name will be merged into one)

## Installation

### Docker

```sh
docker pull 421p/mophpidy
```
ARM is also supported (Raspberry PI and such)
```sh
docker pull 421p/mophpidy:arm32v7
```

Docker Compose example:
```yml
# docker-compose.yml
version: '3'

services:
  bot:
    image: 421p/mophpidy
    restart: always
    environment:
      MOPIDY_URI: 'ws://mopidyhost:6680/mopidy/ws/'
      TELEGRAM_TOKEN: 'TELEGRAM_BOT_TOKEN'
      BOT_USERNAME: '@TELEGRAM_BOT_USERNAME'
      ALLOWED_USERS: '11223344' # supports multiple users '111222333, 14224124'
```

As you can see you have to provide 4 environment variables:

* MOPIDY_URI - uri to mopidy websocket endpoint
* TELEGRAM_TOKEN - bot token
* BOT_USERNAME - bot username
* ALLOWED_USERS - one or more telegram user id, only this users can use a bot

### Non-docker

1) Create `.env` file with 4 required environment variables

```sh
# .env
MOPIDY_URI='ws://mopidyhost:6680/mopidy/ws/'
TELEGRAM_TOKEN='TELEGRAM_BOT_TOKEN'
BOT_USERNAME='@TELEGRAM_BOT_USERNAME'
ALLOWED_USERS='11223344'
```
2) Install dependencies:
```
composer install
```
3) Run bot
```
php bot.php
```

It's highly recommended to install `event` pecl extension for running bot but not required.
