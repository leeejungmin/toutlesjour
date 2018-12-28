<?php
{
"data" : "{{ col.filter_name }}",
"render": function ( data, type, row ) {
    if (type == "display") {
        var html = '';
        if ( !row.hasOwnProperty('{{ col.filter_name }}') )
            return html;
        var {{ col.filter_name }} = row.{{ col.filter_name }};
        var linkVariableForEmployee = linkPrefix+'/{{ col.link }}/employee/'+row.id;
        //Add the link only when we the link is defined so we have to build it or copy it from the avatar section
        {% if col.link is defined and col.link != "" %}
            //if(tempSalaryLink!=""){
                var html = '<a href="'+tempSalaryLink+'">'+row.name+'</a>';
            //}else{
            //    var html = '<a href="#">'+row.name+'</a>';
            //}
            return html;
        {% else %}
            var html = '<a href="#">'+row.name+'</a>';
            return html;
        {% endif %}
    } else {
        return  row.{{ col.filter_name }};
    }
}},
