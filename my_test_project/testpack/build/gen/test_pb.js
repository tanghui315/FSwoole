/**
 * @fileoverview
 * @enhanceable
 * @public
 */
// GENERATED CODE -- DO NOT EDIT!

var jspb = require('google-protobuf');
var goog = jspb;
var global = Function('return this')();

goog.exportSymbol('proto.TestRequest', null, global);
goog.exportSymbol('proto.TestResponse', null, global);

/**
 * Generated by JsPbCodeGenerator.
 * @param {Array=} opt_data Optional initial data array, typically from a
 * server response, or constructed directly in Javascript. The array is used
 * in place and becomes part of the constructed object. It is not cloned.
 * If no data is provided, the constructed object will be empty, but still
 * valid.
 * @extends {jspb.Message}
 * @constructor
 */
proto.TestRequest = function(opt_data) {
  jspb.Message.initialize(this, opt_data, 0, -1, null, null);
};
goog.inherits(proto.TestRequest, jspb.Message);
if (goog.DEBUG && !COMPILED) {
  proto.TestRequest.displayName = 'proto.TestRequest';
}


if (jspb.Message.GENERATE_TO_OBJECT) {
/**
 * Creates an object representation of this proto suitable for use in Soy templates.
 * Field names that are reserved in JavaScript and will be renamed to pb_name.
 * To access a reserved field use, foo.pb_<name>, eg, foo.pb_default.
 * For the list of reserved names please see:
 *     com.google.apps.jspb.JsClassTemplate.JS_RESERVED_WORDS.
 * @param {boolean=} opt_includeInstance Whether to include the JSPB instance
 *     for transitional soy proto support: http://goto/soy-param-migration
 * @return {!Object}
 */
proto.TestRequest.prototype.toObject = function(opt_includeInstance) {
  return proto.TestRequest.toObject(opt_includeInstance, this);
};


/**
 * Static version of the {@see toObject} method.
 * @param {boolean|undefined} includeInstance Whether to include the JSPB
 *     instance for transitional soy proto support:
 *     http://goto/soy-param-migration
 * @param {!proto.TestRequest} msg The msg instance to transform.
 * @return {!Object}
 */
proto.TestRequest.toObject = function(includeInstance, msg) {
  var f, obj = {
    action: jspb.Message.getFieldWithDefault(msg, 1, ""),
    name: jspb.Message.getFieldWithDefault(msg, 2, ""),
    age: jspb.Message.getFieldWithDefault(msg, 3, 0)
  };

  if (includeInstance) {
    obj.$jspbMessageInstance = msg;
  }
  return obj;
};
}


/**
 * Deserializes binary data (in protobuf wire format).
 * @param {jspb.ByteSource} bytes The bytes to deserialize.
 * @return {!proto.TestRequest}
 */
proto.TestRequest.deserializeBinary = function(bytes) {
  var reader = new jspb.BinaryReader(bytes);
  var msg = new proto.TestRequest;
  return proto.TestRequest.deserializeBinaryFromReader(msg, reader);
};


/**
 * Deserializes binary data (in protobuf wire format) from the
 * given reader into the given message object.
 * @param {!proto.TestRequest} msg The message object to deserialize into.
 * @param {!jspb.BinaryReader} reader The BinaryReader to use.
 * @return {!proto.TestRequest}
 */
proto.TestRequest.deserializeBinaryFromReader = function(msg, reader) {
  while (reader.nextField()) {
    if (reader.isEndGroup()) {
      break;
    }
    var field = reader.getFieldNumber();
    switch (field) {
    case 1:
      var value = /** @type {string} */ (reader.readString());
      msg.setAction(value);
      break;
    case 2:
      var value = /** @type {string} */ (reader.readString());
      msg.setName(value);
      break;
    case 3:
      var value = /** @type {number} */ (reader.readInt32());
      msg.setAge(value);
      break;
    default:
      reader.skipField();
      break;
    }
  }
  return msg;
};


/**
 * Serializes the message to binary data (in protobuf wire format).
 * @return {!Uint8Array}
 */
proto.TestRequest.prototype.serializeBinary = function() {
  var writer = new jspb.BinaryWriter();
  proto.TestRequest.serializeBinaryToWriter(this, writer);
  return writer.getResultBuffer();
};


/**
 * Serializes the given message to binary data (in protobuf wire
 * format), writing to the given BinaryWriter.
 * @param {!proto.TestRequest} message
 * @param {!jspb.BinaryWriter} writer
 */
proto.TestRequest.serializeBinaryToWriter = function(message, writer) {
  var f = undefined;
  f = message.getAction();
  if (f.length > 0) {
    writer.writeString(
      1,
      f
    );
  }
  f = message.getName();
  if (f.length > 0) {
    writer.writeString(
      2,
      f
    );
  }
  f = message.getAge();
  if (f !== 0) {
    writer.writeInt32(
      3,
      f
    );
  }
};


/**
 * optional string action = 1;
 * @return {string}
 */
proto.TestRequest.prototype.getAction = function() {
  return /** @type {string} */ (jspb.Message.getFieldWithDefault(this, 1, ""));
};


/** @param {string} value */
proto.TestRequest.prototype.setAction = function(value) {
  jspb.Message.setField(this, 1, value);
};


/**
 * optional string name = 2;
 * @return {string}
 */
proto.TestRequest.prototype.getName = function() {
  return /** @type {string} */ (jspb.Message.getFieldWithDefault(this, 2, ""));
};


/** @param {string} value */
proto.TestRequest.prototype.setName = function(value) {
  jspb.Message.setField(this, 2, value);
};


/**
 * optional int32 age = 3;
 * @return {number}
 */
proto.TestRequest.prototype.getAge = function() {
  return /** @type {number} */ (jspb.Message.getFieldWithDefault(this, 3, 0));
};


/** @param {number} value */
proto.TestRequest.prototype.setAge = function(value) {
  jspb.Message.setField(this, 3, value);
};



/**
 * Generated by JsPbCodeGenerator.
 * @param {Array=} opt_data Optional initial data array, typically from a
 * server response, or constructed directly in Javascript. The array is used
 * in place and becomes part of the constructed object. It is not cloned.
 * If no data is provided, the constructed object will be empty, but still
 * valid.
 * @extends {jspb.Message}
 * @constructor
 */
proto.TestResponse = function(opt_data) {
  jspb.Message.initialize(this, opt_data, 0, -1, null, null);
};
goog.inherits(proto.TestResponse, jspb.Message);
if (goog.DEBUG && !COMPILED) {
  proto.TestResponse.displayName = 'proto.TestResponse';
}


if (jspb.Message.GENERATE_TO_OBJECT) {
/**
 * Creates an object representation of this proto suitable for use in Soy templates.
 * Field names that are reserved in JavaScript and will be renamed to pb_name.
 * To access a reserved field use, foo.pb_<name>, eg, foo.pb_default.
 * For the list of reserved names please see:
 *     com.google.apps.jspb.JsClassTemplate.JS_RESERVED_WORDS.
 * @param {boolean=} opt_includeInstance Whether to include the JSPB instance
 *     for transitional soy proto support: http://goto/soy-param-migration
 * @return {!Object}
 */
proto.TestResponse.prototype.toObject = function(opt_includeInstance) {
  return proto.TestResponse.toObject(opt_includeInstance, this);
};


/**
 * Static version of the {@see toObject} method.
 * @param {boolean|undefined} includeInstance Whether to include the JSPB
 *     instance for transitional soy proto support:
 *     http://goto/soy-param-migration
 * @param {!proto.TestResponse} msg The msg instance to transform.
 * @return {!Object}
 */
proto.TestResponse.toObject = function(includeInstance, msg) {
  var f, obj = {
    nums: jspb.Message.getFieldWithDefault(msg, 4, 0),
    memberid: jspb.Message.getFieldWithDefault(msg, 5, 0)
  };

  if (includeInstance) {
    obj.$jspbMessageInstance = msg;
  }
  return obj;
};
}


/**
 * Deserializes binary data (in protobuf wire format).
 * @param {jspb.ByteSource} bytes The bytes to deserialize.
 * @return {!proto.TestResponse}
 */
proto.TestResponse.deserializeBinary = function(bytes) {
  var reader = new jspb.BinaryReader(bytes);
  var msg = new proto.TestResponse;
  return proto.TestResponse.deserializeBinaryFromReader(msg, reader);
};


/**
 * Deserializes binary data (in protobuf wire format) from the
 * given reader into the given message object.
 * @param {!proto.TestResponse} msg The message object to deserialize into.
 * @param {!jspb.BinaryReader} reader The BinaryReader to use.
 * @return {!proto.TestResponse}
 */
proto.TestResponse.deserializeBinaryFromReader = function(msg, reader) {
  while (reader.nextField()) {
    if (reader.isEndGroup()) {
      break;
    }
    var field = reader.getFieldNumber();
    switch (field) {
    case 4:
      var value = /** @type {number} */ (reader.readInt32());
      msg.setNums(value);
      break;
    case 5:
      var value = /** @type {number} */ (reader.readInt32());
      msg.setMemberid(value);
      break;
    default:
      reader.skipField();
      break;
    }
  }
  return msg;
};


/**
 * Serializes the message to binary data (in protobuf wire format).
 * @return {!Uint8Array}
 */
proto.TestResponse.prototype.serializeBinary = function() {
  var writer = new jspb.BinaryWriter();
  proto.TestResponse.serializeBinaryToWriter(this, writer);
  return writer.getResultBuffer();
};


/**
 * Serializes the given message to binary data (in protobuf wire
 * format), writing to the given BinaryWriter.
 * @param {!proto.TestResponse} message
 * @param {!jspb.BinaryWriter} writer
 */
proto.TestResponse.serializeBinaryToWriter = function(message, writer) {
  var f = undefined;
  f = message.getNums();
  if (f !== 0) {
    writer.writeInt32(
      4,
      f
    );
  }
  f = message.getMemberid();
  if (f !== 0) {
    writer.writeInt32(
      5,
      f
    );
  }
};


/**
 * optional int32 nums = 4;
 * @return {number}
 */
proto.TestResponse.prototype.getNums = function() {
  return /** @type {number} */ (jspb.Message.getFieldWithDefault(this, 4, 0));
};


/** @param {number} value */
proto.TestResponse.prototype.setNums = function(value) {
  jspb.Message.setField(this, 4, value);
};


/**
 * optional int32 memberId = 5;
 * @return {number}
 */
proto.TestResponse.prototype.getMemberid = function() {
  return /** @type {number} */ (jspb.Message.getFieldWithDefault(this, 5, 0));
};


/** @param {number} value */
proto.TestResponse.prototype.setMemberid = function(value) {
  jspb.Message.setField(this, 5, value);
};


goog.object.extend(exports, proto);
