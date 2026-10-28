const express = require('express');
const http = require('http');
const socketio = require('socket.io');
const Server = socketio.Server;
const authMiddleware = require('./middleware/auth');
const registerChatHandlers = require('./sockets/chats');