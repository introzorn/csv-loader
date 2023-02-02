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
    init2();
});

function init() {



    if (!!$(".csvfile-grid")) { return; }


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

function init2() {

    if (!!!$(".csvfile-grid")) { return }

    window.addEventListener('resize', function (e) {


        $h = $(".csvfile-panel").clientHeight;
        $(".csvfile-grid").style.setProperty("margin-top", ($h + 120) + "px");
   

    })

window.dispatchEvent(new Event("resize"));
$("html").style.setProperty("overflow-x","visible");
$("body").style.setProperty("overflow-x","visible");

    $(".first-row-check").addEventListener('change', function (e) {
        if(e.target.checked){
            $(".first-row").classList.add("styck");
        }else{
            $(".first-row").classList.remove("styck");
        }
    });


}




function csvDownload() {
    $("form").submit();
}