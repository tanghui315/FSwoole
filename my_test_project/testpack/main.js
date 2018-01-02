var bp = require('bufferpack');
var testMeg = require('./build/gen/test_pb');
var DataParser = require('./build/dataparser');

var dp = new DataParser(function (pack) {
    //解析data
     var uint8View = new Uint8Array(pack.data);
     var tmsg=testMeg.TestResponse.deserializeBinary(uint8View);
     var info = {};
     info.nums=tmsg.getNums();
     info.mid=tmsg.getMemberid();
     console.dir(info);
});
function getByteLen(val) {
    var len = 0;
    for (var i = 0; i < val.length; i++) {
        if (val[i].match(/[^\x00-\xff]/ig) != null) //全角
            len += 2;
        else
            len += 1;
    }
    return len;
}

function mergeArrayBuffer(buff1, buff2) {
    var len1 = buff1.byteLength;
    var len2 = buff2.byteLength;
    var ret = new ArrayBuffer(len1 + len2);
    var retI8a = new Int8Array(ret);
    var i8a = new Int8Array(buff1);
    var i = 0;
    for (i = 0; i < len1; i++) {
        retI8a[i] = i8a[i];
    }
    i8a = new Int8Array(buff2);
    for (i = 0; i < len2; i++) {
        retI8a[len1 + i] = i8a[i];
    }
    return ret;
}

function lengthInUtf8Bytes(str) {
    // Matches only the 10.. bytes that are non-initial characters in a multi-byte sequence.
    var m = encodeURIComponent(str).match(/%[89ABab]/g);
    return str.length + (m ? m.length : 0);
}



//var msg = { type: 1, content: "你是谁", remark: "test pack" };
//var msgJson = JSON.stringify(msg);
var message = new testMeg.TestRequest();
//message.setAction("test");
message.setName("测试1");
message.setAge(23);
var bodyBuff = message.serializeBinary();
var cmd = 0;
//var len = lengthInUtf8Bytes(body);
var len = bodyBuff.length;
//var format = '>ii';
var format = 'IH';
console.log(len);
console.log(bodyBuff);
var packed = bp.pack(format, [len, cmd]);
var headerBuff = packed.buffer;
var allBuff = mergeArrayBuffer(headerBuff, bodyBuff.buffer);
console.log(allBuff);

var socket = new WebSocket("ws://127.0.0.1:3653/");

//打开连接时触发
socket.onopen = function () {
    console.log('open and connect');
    socket.send(allBuff);
};
//收到消息时触发
socket.onmessage = function (evt) {
    var data = evt.data;
    var arrayBuffer;
    var fileReader = new FileReader();
    fileReader.readAsArrayBuffer(data);
    fileReader.onload = function () {
        arrayBuffer = this.result;
        console.log(arrayBuffer);
        dp.parseData(arrayBuffer);
    };
    
    
};
//关闭连接时触发
socket.onclose = function (evt) {
    console.log('close');
}
//连接错误时触发
socket.onerror = function (evt) {
    console.log('err');
}



