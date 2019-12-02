const net = require('net');
const port = 8888;
const host = '127.0.0.1';

const server = net.createServer();
server.listen(port, host, () => {
    console.log('TCP Server is running on port ' + port + '.');
});

let sockets = [];

server.on('connection', function(sock) {
    console.log('CONNECTED: ' + sock.remoteAddress + ':' + sock.remotePort);
    sockets.push(sock);

    sock.on('data', function(data) {
        try {
            const object = JSON.parse(data.toString('utf8'));
            console.log('____________________________________________________________');
            console.log('# ' + object.channel + ' | ' + object.level_name + ' | ' + object.datetime);
            console.log('------------------------------------------------------------');
            console.log('Message: ' + object.message);
            console.log('Data: ');
            console.log(JSON.stringify(object.context, null, 2));
        } catch(e) {
            console.log('ERROR: ');
            console.log(e);
        }

        // Write the data back to all the connected, the client will receive it as data from the server
        // sockets.forEach(function(sock, index, array) {
        //     sock.write(sock.remoteAddress + ':' + sock.remotePort + " said " + data + '\n');
        // });
    });

    // Add a 'close' event handler to this instance of socket
    sock.on('close', function(data) {
        let index = sockets.findIndex(function(o) {
            return o.remoteAddress === sock.remoteAddress && o.remotePort === sock.remotePort;
        })
        if (index !== -1) sockets.splice(index, 1);
        console.log('CLOSED: ' + sock.remoteAddress + ' ' + sock.remotePort);
    });

    sock.on('error', function(data) {
        console.log('ERROR: ' + data);
    });

    sock.on('uncaughtException', function(data) {
        console.log('uncaughtException: ' + data);
    });
});