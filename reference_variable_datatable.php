<?php
{% elseif col.render_type is defined and col.render_type == "variable" %}
    {"orderable": false,
    "searchable": false,
    "data": function ( row, type, set ) {
        var code = '{{ col.render_param.variable }}';
        if (code in row.variables) {
            return row.variables[code];
        }

        return null;
    },
    "render": function ( data, type, row ) {
        var content;
        var code = '{{ col.render_param.variable }}';
        var locked = '{{ col.render_param.locked }}';
        var $span = $('<span>');
        $span.addClass('variable-item');

        if (code in row.variables) {
            var value = row.variables[code].amount;

            //Specific condition for the comment !
            if(row.variables[code].code==99999){
                value = value;
            }else{
                //value = DigipayUtils.moneyNumber(value);
                value = value;
            }

            if(locked) {
                $span.addClass('variable-locked');
                if (value !== null) {
                    value = '<i class="fa fa-lock"></i> ' + value;
                }
            }
            else {
                $span.addClass('variable-editable');
                if (value !== null) {
                    //$span.addClass('variable-euro');
                }
            }
            $span.html(value);
        }
        else {
            $span.addClass('variable-off');
        }
        content = $span.prop('outerHTML');

        return content;
    }},
{% else %}
    {"data": "{{ col.filter_name }}",
    "render": function ( data, type, row ) {
      return 'jungmin';
    }
    },
{% endif %}
{% endfor %}

{% if not tableSettings.hideAction is defined or not tableSettings.hideAction %}
{"render": function ( data, type, row ) {
var html = '';
if ( !row.hasOwnProperty('actions') )
    return html;
var actions = row.actions;
if (actions.length == 1) {
    var blank = "";
    if(actions[0].class){
        if(actions[0].class.search("_blank")!=-1){
            blank = 'target="_blank"';
        }
    }

    html = '<a class="btn btn-default" href="' + actions[0].url + '" '+blank+'> ' + actions[0].label + '</button>';
} else if (actions.length > 1) {
    var blank = "";
    if(actions[0].class) {
        if (actions[0].class.search("_blank") != -1) {
            blank = 'target="_blank"';
        }
    }
    html = '<div class="btn-group"><a class="btn btn-default" href="' + actions[0].url + ' '+blank+'">' +  actions[0].label +'</a><a class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-angle-down"></i></a><ul class="dropdown-menu" role="menu">';
    for (var i = 1; i < actions.length; i++) {
        var blank = "";
        if(actions[0].class) {
            if (actions[i].class.search("_blank") != -1) {
                blank = 'target="_blank"';
            }
        }
        if(actions[i].class){
            html += '<li><a class="'+actions[i].class+'" href="'+ actions[i].url +'" '+blank+'>'+ actions[i].label +'</a></li>';
        }else{
            html += '<li><a href="'+ actions[i].url +'">'+ actions[i].label +'</a></li>';
        }

    }
    html += '</ul></div>';
}
