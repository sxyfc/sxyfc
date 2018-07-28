/*global Qiniu */
/*global plupload */
/*global FileProgress */
/*global hljs */

function calculate(file,callBack){
    var fileReader = new FileReader(),
        blobSlice = File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice,
        chunkSize = 2097152,
        // read in chunks of 2MB
        chunks = Math.ceil(file.size / chunkSize),
        currentChunk = 0,
        spark = new SparkMD5();

    fileReader.onload = function(e) {
        spark.appendBinary(e.target.result); // append binary string
        currentChunk++;

        if (currentChunk < chunks) {
            console.log(currentChunk);
            loadNext();
        }
        else {
            callBack(spark.end());
        }
    };

    function loadNext() {

        var start = currentChunk * chunkSize,
            end = start + chunkSize >= file.size ? file.size : start + chunkSize;

        fileReader.readAsBinaryString(blobSlice.call(file  , start, end));
    };

    loadNext();
}


function calculate_start_end(file, callBack){

    var fileReader = new FileReader(),
        blobSlice = File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice,
        chunkSize = 2097152,
        // read in chunks of 2MB
        chunks = Math.ceil(file.size / chunkSize),
        currentChunk = 0,
        spark = new SparkMD5();

    fileReader.onload = function(e) {
        spark.appendBinary(e.target.result); // append binary string
        currentChunk++;

        if (currentChunk < chunks) {
            loadNext();
        }
        else {
            callBack(spark.end());
        }
    };

    function loadNext() {

        console.log(currentChunk);
        if(currentChunk == 0 || currentChunk == chunks ){
            var start = currentChunk * chunkSize;
            var    end = start + chunkSize >= file.size ? file.size : start + chunkSize;
            fileReader.readAsBinaryString(blobSlice.call(file  , start, end));
        }else{
            currentChunk++;
            loadNext();
        }
    };

    loadNext();
}


$(function () {
    var uploader = Qiniu.uploader({
        disable_statistics_report: false,
        runtimes: 'html5,flash,html4',
        browse_button: 'pickfiles_' + field_name,
        container: 'container_' + field_name ,
        drop_element: 'container_'  + field_name ,
        max_file_size: '10000mb',
        flash_swf_url: 'bower_components/plupload/js/Moxie.swf',
        dragdrop: true,
        chunk_size: '4mb',
        multi_selection: !(moxie.core.utils.Env.OS.toLowerCase() === "ios"),
        uptoken_func : function(){
            return qiniu_token
        },
        domain: $('#domain_' + field_name).val(),
        get_new_uptoken: false,
        //downtoken_url: '/downtoken',
        unique_names: false,
        save_key: false,
        x_vars: {
        //     'id': '1234',
        //    'time': function(up, file) {
        //         var time = (new Date()).getTime();
        //         // do something with 'time'
        //         return time;
        //    },
        },
        auto_start: true,
        log_level: 0,
        init: {
            'BeforeChunkUpload': function (up, file) {

            },

            'FilesAdded': function (up, files) {

                $('table').show();
                $('#success').hide();
                plupload.each(files, function (file) {
                    var progress = new FileProgress(file,
                        'fsUploadProgress_'    + field_name);
                    progress.setStatus("上传中...");
                    progress.bindUploadCancel(up);
                });
            },
            'BeforeUpload': function (up, file) {

                console.log("this is a beforeupload function from init");
                var that = this;
                calculate_start_end(file.getNative() , function (res) {
                    //todo check md5
                    file.md5 = res;
                    $.get(check_md5 , {
                        md5 : file.md5
                    } , function ($res) {
                        console.log($res);
                        $res.key = $res.data.url;
                        //todo if file exists
                        if($res.data.ifExist == 1){
                            file.uploaded =1;
                            up.removeFile(file);
                            var progress = new FileProgress(file, 'fsUploadProgress_'   + field_name );
                            progress.setProgress("100%");
                            //
                            console.log(file);
                            $.post(save_attach , {
                                site_id : site_id ,
                                url : $res.data.url ,
                                type : "Qiniu" ,
                                md5 : file.md5 ,
                                filesize : file.filesize
                            } , function ($res) {

                                console.log($res);
                                progress.setComplete(up , JSON.stringify($res));
                                //todo add hidden form control
                                var $hidden_form = '<span class="success"><input type="hidden" value="'+ $res.data.file_id + '" name="'+real_form_name+'"></span>';
                                if( $("#"  + file.id + " .progressName").find('.success').length ==1 ){

                                }else{
                                    $("#"  + file.id + " .progressName").append($hidden_form);
                                }
                            },'json');

                        }else{
                            file.uploaded =0;
                            var progress = new FileProgress(file, 'fsUploadProgress_'   + field_name );
                            var chunk_size = plupload.parseSize(that.getOption(
                                'chunk_size'));
                            if (up.runtime === 'html5' && chunk_size) {
                            //    progress.setChunkProgess(chunk_size);
                            }
                        }
                    } , 'json');
                });

            },
            'UploadProgress': function (up, file) {
                var progress = new FileProgress(file, 'fsUploadProgress_'   + field_name );
                var chunk_size = plupload.parseSize(this.getOption(
                    'chunk_size'));
                progress.setProgress(file.percent + "%", file.speed,
                    chunk_size);
            },
            'UploadComplete': function () {
                $('#success').show();
            },
            'FileUploaded': function (up, file, info) {
                if(file.uploaded==1){
                    return;
                }
                console.log(info.response);
                var response = JSON.parse(info.response)
                //todo save attach
                $.post(save_attach , {
                    site_id : site_id ,
                    url : response.key ,
                    type : "Qiniu" ,
                    md5 : file.md5 ,
                    filesize : file.filesize
                } , function ($res) {

                    console.log($res);
                    //todo add hidden form control
                    var $hidden_form = '<span class="success"><input type="hidden" value="'+ $res.data.file_id + '" name="'+real_form_name+'"></span>';

                    if( $("#"  + file.id + " .progressName").find('.success').length ==1 ){

                    }else{
                        $("#"  + file.id + " .progressName").append($hidden_form);
                    }
                    var progress = new FileProgress(file, 'fsUploadProgress_'  + field_name);
                    progress.setComplete(up, info.response);
                },'json');
            },
            'Key' : function(up, file) {
                var key = user_id + "/" + file.name;
                // do something with key
                return key
            },
            'Error': function (up, err, errTip) {
                $('table').show();
                var progress = new FileProgress(err.file, 'fsUploadProgress_'  + field_name);
                progress.setError();
                progress.setStatus(errTip);
            }
        }
    });
    //uploader.init();
    uploader.bind('BeforeUpload', function () {
        console.log("hello man, i am going to upload a file");
    });
    uploader.bind('FileUploaded', function ($res) {
        console.log($res);
        console.log('hello man,a file is uploaded');
    });

    $('body').on('click', 'table button.btn', function () {
        $(this).parents('tr').next().toggle();
    });

});