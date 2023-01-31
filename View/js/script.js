//удобная обертка
$ = function (selsctor, obj = null) {
    if (obj === null) { obj = document; }
    return obj.querySelector(selsctor);
}
$$ = function (selsctor, obj = null) {
    if (obj === null) { obj = document; }
    return obj.querySelectorAll(selsctor);
}

$T = function (callback, delay = 1, $arg = null) {
    setTimeout(callback, delay, $arg);
}


window.addEventListener("DOMContentLoaded", function () {

    init();

});

function init() {
    window.addEventListener('dragenter', function (e) {
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';



    }, false);


    window.addEventListener('drop', function (e) {

        e.stopPropagation();
        e.preventDefault();

        if (e.dataTransfer.files[0].type != "application/vnd.ms-excel") { return false; }

        let dT = new DataTransfer();
        dT.items.add(e.dataTransfer.files[0]);

        $(".download-button input[type=file]").files = dT.files;

       $(".download-button small").innerHTML = e.dataTransfer.files[0].name;
       
       csvDownload();
    }, false);


    window.addEventListener('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
    });
    $(".download-button input[type=file]").addEventListener('change', function (e) {
        $(".download-button small").innerHTML = e.target.files[0].name;
        csvDownload();
    });
}



function csvDownload(){
 $("form").submit();
}