<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: test.proto

namespace GPBMetadata;

class Test
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(hex2bin(
            "0a6e0a0a746573742e70726f746f22280a0b546573745265717565737412" .
            "0c0a046e616d65180120012809120b0a03616765180220012805222e0a0c" .
            "54657374526573706f6e7365120c0a046e756d7318032001280512100a08" .
            "6d656d6265724964180420012805620670726f746f33"
        ));

        static::$is_initialized = true;
    }
}
