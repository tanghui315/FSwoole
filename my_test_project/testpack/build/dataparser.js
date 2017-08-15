var bp = require('bufferpack');

module.exports = function (callback) {
    return new DataParser(callback);
};

var DataParser = function (callback) {
    this._buffer = null;
    this._headerSize = 8;
    this._callback = callback;

};

var pro = DataParser.prototype;

pro.parseData = function (arrBuffer) {
    console.dir(arrBuffer);
    if (!arrBuffer) return;
    this._fillBuffer(arrBuffer);
    if (!this._checkHeader()) return;
    var pack = this._parseHeader(this._buffer);
    console.dir(pack);
    if (!this._checkBody(pack)) return;
    pack.data = this.copyArrayBuffer(this._buffer, this._headerSize, pack.bodySize);
    //解析data
    this._parseBody(pack);
    var packTotalLen = this._headerSize + pack.bodySize;
    if (arrBuffer.byteLength > packTotalLen) {
        console.log("byteLength > packTotalLen-->出现粘包");
        var buffer = this.copyArrayBuffer(this._buffer, packTotalLen);
        this._buffer = null;
        this.parseData(buffer);
    } else {
        this._buffer = null;
    }
};

pro.copyArrayBuffer = function (buff, offset, len) {
    var buffLen = buff.byteLength;
    var retArrayBuff = null;
    var retLen = len;
    if (!len) {
        len = buffLen - offset;
    }
    if (len == 0) {
        return null;
    }
    retArrayBuff = new ArrayBuffer(len);
    var reti8a = new Int8Array(retArrayBuff);
    var i8a = new Int8Array(buff, offset);
    for (var i = 0; i < len; i++) {
        reti8a[i] = i8a[i];
    }
    return retArrayBuff;
};
pro._checkBody = function (pack) {
    if (pack.bodySize > this._buffer.byteLength - this._headerSize) {
        console.log('-->body data not enough');
        return false;
    }
    return true;
};

pro._parseHeader = function (buff) {

    // var format = '<ii5s';
    // var unpacked = bp.unpack(format,buff,0);
    // console.dir(unpacked);
    // var len =unpacked[0];
    // var cmd=unpacked[1];
    // var pack = {
    //       cmd:cmd,
    //       bodySize:len,
    //       data:null
    // }
    var dv = new DataView(buff);
    var pack = {
        cmd: dv.getInt32(4),
        bodySize: dv.getInt32(0),
        data: null
    }
    return pack;
};

pro._fillBuffer = function (arrBuffer) {
    if (!this._buffer) {
        this._buffer = arrBuffer;
    } else {
        this._buffer = this.mergeArrayBuffer(this._buffer, arrBuffer);
    }
};

pro._checkHeader = function () {
    if (this._buffer.byteLength < this._headerSize) {
        console.log('-->buffer bytelength less than header size min limit');
        return false;
    }
    return true;
};
pro._parseBody = function (pack) {
    if (this._callback) {
        this._callback(pack);
    }
};

pro.mergeArrayBuffer = function (buff1, buff2) {
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
};