"use strict";

let app = require('express')();

app.get('/', function (request, response) {
    response.end('<a href="feed/blog.atom" title="all blogposts">Feed</a>Home <a href="about">About</a>');
});

app.get('/about', function (request, response) {
    response.end('About');
});

app.get('/feed/blog.atom', function (request, response) {
    response.end('Feed');
});

let server = app.listen(8080, function () {
    const host = 'localhost';
    const port = server.address().port;

    console.log('Testing server listening at http://%s:%s', host, port);
});
