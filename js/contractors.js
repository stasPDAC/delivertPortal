$(document).ready(function () {
    $('#projects_table').DataTable({
        columnDefs: [
            {orderable: false, targets: [6]}
        ],
        order: [[5, 'desc']],
        pageLength: 50,
        language: {
            search: "",
            searchPlaceholder: "חיפוש בכל השדות",
            lengthMenu: "הצג _MENU_ בעמוד",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין קבלני ביצוע",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ קבלני ביצוע",
            infoFiltered: "(מסונן מתוך _MAX_ קבלני ביצוע)",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
        initComplete: function(settings, Json){
            /* Add a select input to filter by status*/
            var container = $('<div style="float:right; margin-right:24px;"></div>')
            var select = $('<select style="margin-right: 24px;"><option value="">הצג את כל הסטטוסים</option></select>');
            container.append(select);
            $('#posts-table_filter').after(container);


            var column = this.api().column(1);
            select.on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                );

                column
                    .search( val ? '^'+val+'$' : '', true, false )
                    .draw();
            } );

            column.data().pluck('stStatus').unique().sort().each( function ( d, j ) {
                select.append( '<option value="'+d+'">'+d+'</option>' )
            } );


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
        const newLeft = Math.max(0, posRight - width_list -120);
        // console.log('posLeft' + posLeft);
        // console.log('posRight' + posRight);
        // console.log('width_td' + width_td);


        $(list).css("top",newTop + "px");
        $(list).css("left",newLeft + "px");

    }, function(){

    });

})