const net = require('net');
const JSONStream = require('pixl-json-stream');

const port = 8888;
const host = '127.0.0.1';

const server = net.createServer();
server.listen(port, host, () => {
    console.log('TCP Server is running on port ' + port + '.');
});

let sockets = [];

server.on('connection', function(socket) {
    console.log('CONNECTED: ' + socket.remoteAddress + ':' + socket.remotePort);
    sockets.push(socket);

    // new connection, attach JSON stream handler
    const stream = new JSONStream(socket);
    stream.on('json', function(object) {
        // got gata from client
        // console.log("Received data from client: ", object);
        console.log('____________________________________________________________');
        console.log('# ' + object.channel + ' | ' + object.level_name + ' | ' + object.datetime);
        console.log('------------------------------------------------------------');
        console.log('Message: ' + object.message);
        console.log('Data: ');
        console.log(JSON.stringify(object.context, null, 2));

        // send response
        // stream.write({ code: 1234, description: "We hear you" });
    } );

    // Add a 'close' event handler to this instance of socket
    socket.on('close', function(data) {
        let index = sockets.findIndex(function(o) {
            return o.remoteAddress === socket.remoteAddress && o.remotePort === socket.remotePort;
        })
        if (index !== -1) sockets.splice(index, 1);
        console.log('CLOSED: ' + socket.remoteAddress + ' ' + socket.remotePort);
    });

    socket.on('error', function(data) {
        console.log('ERROR: ' + data);
    });

    socket.on('uncaughtException', function(data) {
        console.log('uncaughtException: ' + data);
    });
});