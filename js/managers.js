$(document).ready(function () {
    $('#projects_table').DataTable({
        columnDefs: [
            {orderable: false, targets: [5]}
        ],
        order: [[4, 'desc']],
        pageLength: 50,
        language: {
            search: "",
            searchPlaceholder: "חיפוש בכל השדות",
            lengthMenu: "הצג _MENU_ בעמוד",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין מנהלי פרויקטים",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ מנהלי פרויקטים",
            infoFiltered: "(מסונן מתוך _MAX_ מנהלי פרויקטים)",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        }
    });

    setTimeout(() => {
        const box = document.getElementById('msg')
        if(box){
            box.style.display = 'none';
        }
    }, 2000);

    $(".td_link").hover(function(){
        let list = $(this).find(".td_hover_list").first();
        // let td = $(this).find(".td_list").first();
        const boundingBox = $("#projects_table_wrapper").offset();
        const boundingBoxHeight = $("#projects_table_wrapper").outerHeight();

        const outerH = $(list).outerHeight();
        const outerW = $(list).outerWidth();
        const pos = $(this).offset();

        let posTop = pos.top - $(window).scrollTop();
        let posLeft = pos.left;


        const windowWidth = $(window).width();
        const windowHeight = $(window).height();

        const width_list = $(list).width();
        // const width_td = $(td).width();
        const height_list = $(list).height();
        // console.log('width_td' + width_td);
        let posRight = pos.left + $(this).outerWidth();

        if((pos.top + height_list - (height_list / 2))  > ($(window).scrollTop() + windowHeight) ){
            const d = (pos.top + height_list  - (height_list / 2)) - ($(window).scrollTop() + windowHeight)
            posTop -= d + 20
            // console.log('oob bottom');
        }

        if((pos.top - (height_list / 2)) < ($(window).scrollTop()) ){
            const d = ($(window).scrollTop()) - (pos.top - (height_list / 2))
            posTop += d + 20
            // console.log('oob top');
        }

        // console.log("posRight", posRight)
        const newTop = Math.max(0, posTop);
        // const newLeft = Math.max(0, posLeft - width_list + 50);
        const newLeft = Math.max(0, posRight - width_list -50);
        // console.log('posLeft' + posLeft);
        // console.log('posRight' + posRight);
        // console.log('width_td' + width_td);


        $(list).css("top",newTop + "px");
        $(list).css("left",newLeft + "px");

    }, function(){

    });

})