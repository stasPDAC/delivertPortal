$(document).ready(function () {
    setTimeout(() => {
        const box = document.getElementById('msg')
        if(box){
            box.style.display = 'none';
        }
    }, 2000);
    tinymce.init({
        selector: '#articleBodyEditor',
        language: 'he_IL',
        height: '300px',
        plugins: 'image code paste',
        paste_data_images: true
    });


    const acc = document.getElementsByClassName("accordion");
    let i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            const panel = this.nextElementSibling;
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
            }
        });
    }

    const modal = document.getElementById("myModal");
    const btn = document.getElementById("myBtn");
    const span = document.getElementsByClassName("close")[0];
    if(btn){
        btn.onclick = function() {
            modal.style.display = "flex";
        }
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    document.getElementById("step_1").addEventListener("click", FunctionView1);
    document.getElementById("step_2").addEventListener("click", FunctionView2);
    document.getElementById("step_3").addEventListener("click", FunctionView3);
    document.getElementById("info").addEventListener("click", FunctionView4);

    function FunctionView1() {
        document.getElementById("view_1").style.display = "none";
        document.getElementById("view_2").style.display = "block";
    }
    function FunctionView2() {
        document.getElementById("view_2").style.display = "none";
        document.getElementById("view_3").style.display = "block";
    }
    function FunctionView3() {
        document.getElementById("welcome").style.display = "none";
    }
    function FunctionView4() {
        document.getElementById("welcome").style.display = "flex";
        document.getElementById("view_1").style.display = "none";
        document.getElementById("view_2").style.display = "none";
        document.getElementById("view_3").style.display = "block";
    }

    const reportCompletion = document.getElementById("reportCompletion");
    const reportCompletionBtn = document.getElementById("reportCompletionBtn");
    const reportCompletionClose = document.getElementsByClassName("close")[1];
    // if(reportCompletionBtn){
        reportCompletionBtn.onclick = function() {
            reportCompletion.style.display = "flex";
        }
    // }

    reportCompletionClose.onclick = function() {
        reportCompletion.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == reportCompletion) {
            reportCompletion.style.display = "none";
        }
    }


})