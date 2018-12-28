<script>
data.filters = {};
var getUrl = window.location;
var baseUrl = getUrl .protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];



                   //Retrieve Filters
                   $('select.form-filter, input.form-filter', $('#'+ settings.sTableId).parents(".table-container")).each(function() {
                      if($(this).parent('.dataTables_sizing').length == 0) { //On évite les filtres qui sont dans le sizing...
                           data.filters[$(this).attr("name")] = $(this).val();
                      };
                   });
                   //Save Filters
                   if (window.localStorage) {
                       window.localStorage.setItem('DataTableState_' + settings.sTableId + '_' + window.location.pathname, JSON.stringify(data));
                   }

                   return data;
               },
               "stateLoadCallback": function (settings, data) {
                   //Retreive Data
                   if (!data && window.localStorage) {
                       data = JSON.parse(window.localStorage.getItem('DataTableState_' + settings.sTableId + '_' + window.location.pathname));
                   }
                   //Retrieve Filters
                   if (data && data.filters) {
                       $('select.form-filter, input.form-filter', $('#'+ settings.sTableId).parents(".table-container")).each(function() {
                           if($(this).parent('.dataTables_sizing').length == 0
                               && data.filters[$(this).attr("name")]
                               && data.filters[$(this).attr("name")] != $(this).val()) { //On évite les filtres qui sont dans le sizing...
                               $(this).val(data.filters[$(this).attr("name")]);
                               reload = true;
                           };
                       });

                   }

                   return data;
               },
               "ajax": {
                   "url": "{{ tableSettings.ajaxUrl }}" // ajax source
               },
               "columns": [
               /*
                   {"render": function ( data, type, row ) {
                       var html = '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="' + row.id + '"/><span></span></label>';

                       return html;
                   }},
               */




                   {% for col in tableSettings.columns %}
                       {% elseif col.render_type is defined and col.render_type == "checkbox" %}
                           {
                           "data" : "{{ col.filter_name }}",
                           "render": function ( data, type, row ) {
                               if (type == "display") {
                                   var html = '<input type="checkbox" data-id="'+ row.id +'" ' + ((row.{{ col.filter_name }} == 1) ? 'checked' : '') +'>';
                                   return html;
                               } else {
                                   return  row.{{ col.filter_name }};
                               }
                           }},
                       {% elseif col.render_type is defined and col.render_type == "image" %}
                           {
                           "data" : "{{ col.filter_name }}",
                           "render": function ( data, type, row ) {
                               if (type == "display") {
                                   var html = '';
                                   if ( !row.hasOwnProperty('{{ col.filter_name }}') )
                                       return html;
                                   var {{ col.filter_name }} = row.{{ col.filter_name }};
                                   if ({{ col.filter_name }}.length == 1) {
                                       //Add the link only when we have it as parameter
                                       {% if col.link is defined and col.link != "" %}
                                           var str = row.{{ col.filter_name }}[0].url;
                                           var tempstringPos = str.indexOf("/downloader");
                                           var linkPrefix = str.substring(0, tempstringPos);
                                           //if(linkPrefix!=""){
                                               tempSalaryLink = linkPrefix+'/{{ col.link }}/employee/'+row.id;

                                               var html = '<a href="'+linkPrefix+'/{{ col.link }}/employee/'+row.id+'"><img style="width: 45px; height: 45px;" src="'+ row.{{ col.filter_name }}[0].url +'"></a>';
                                           //}else{
                                           //    var html = '<a href="#"><img style="width: 45px; height: 45px;" src="'+ row.{{ col.filter_name }}[0].url +'"></a>';
                                           //}
                                       {% else %}
                                           var html = '<img style="width: 45px; height: 45px;" src="'+ row.{{ col.filter_name }}[0].url +'">';
                                       {% endif %}
                                   }
                                   return html;
                               } else {
                                   return  row.{{ col.filter_name }};
                               }
                           }},
                       {% elseif col.render_type is defined and col.render_type == "seriallink" %}
                       {
                           "data" : "{{ col.filter_name }}",
                           "render": function ( data, type, row ) {
                               if (type == "display") {
                                   var html = '';
                                   if ( !row.hasOwnProperty('{{ col.filter_name }}') )
                                       return html;
                                   var {{ col.filter_name }} = row.{{ col.filter_name }};
                                       //Add the link only when we the link is defined so we have to build it or copy it from the avatar section
                                       {% if col.link is defined and col.link != "" %}
                                           //if(tempSalaryLink!=""){
                                               var html = '<a href="'+tempSalaryLink+'">'+ row.serial+'</a>';
                                           //}else{
                                           //    var html = '<a href="#">'+ row.serial+'</a>';
                                           //}
                                           return html;
                                       {% else %}
                                           var html = '<a href="#">'+row.serial+'</a>';
                                           return html;
                                       {% endif %}

                               } else {
                                   return  row.{{ col.filter_name }};
                               }
                           }},
                       {% elseif col.render_type is defined and col.render_type == "employenamelink" %}
                       {
                           "data" : "{{ col.filter_name }}",
                           "render": function ( data, type, row ) {
                               if (type == "display") {
                                   var html = '';
                                   if ( !row.hasOwnProperty('{{ col.filter_name }}') )
                                       return html;
                                   var {{ col.filter_name }} = row.{{ col.filter_name }};
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
