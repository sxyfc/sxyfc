<?php
/**
 * md5 chunk tmp name
 */
function md5_chunked($chunk) {
    return "{$chunk}.part";
}

function origin_filename($filename) {
    return $filename;
}
