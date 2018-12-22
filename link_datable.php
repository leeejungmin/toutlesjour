<script>
data.filters = {};
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
                       {% if col.render_type is defined and col.render_type == "tick" %}
                           {
                           "data" : "{{ col.filter_name }}",
                           "render": function ( data, type, row ) {
                               if (type == "display") {
                                   var isTrue = row.{{ col.filter_name }} == 1;
                                   return '<i class="fa fa-'+ (isTrue ? 'check' : 'times') +' font-'+ (isTrue ? 'green' : 'red') +'"></i>';
                               } else {
                                   return  row.{{ col.filter_name }};
                               }
                           }},
                       {% elseif col.render_type is defined and col.render_type == "input" %}
                           {
                           "data" : "{{ col.filter_name }}",
                           "render": function ( data, type, row ) {
                               if (type == "display") {
                                   var html = '<input class="form-control {{ col.input_class }}" type="text" data-id="'+ row.id +'" data-target-id="'+ ((row.target_id == null) ? "" : row.target_id) +'" value="'+ ((row.amount == null) ? "" : row.amount) +'">';
                                   //html = '<div class="input-group">' + html + '<span class="input-group-addon"> €</span></div>';
                                   html = '<div class="input-group">' + html + '</div>';
                                   return html;
                               } else {
                                   return  row.amount;
                               }
                           }},
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
                       {% elseif col.render_type is defined and col.render_type == "day" %}
                           {"orderable": false,
                           "searchable": false,
                           "className": 'event-box',
                           "data": function ( row, type, set ) {
                               var data = {};
                               data['employee'] = row.id;
                               var day = moment("{{col.render_day}}", "YYYY-MM-DD");
                               data['day'] = day;
                               data['hour'] = false;
                               data['day_off'] = false;

                               //Check Hours and Schedule
                               if (row.schedule) {
                                   var day_schedule = row.schedule[day.isoWeekday()];
                                   //Set flag to determine if user has worked
                                   var hasHour = false;
                                   {% if not tableSettings.hideHours is defined or not tableSettings.hideHours %}
                                   if (row.hours) {
                                       for (var i = 0; i < row.hours.length; i++) {
                                           var sd = moment(row.hours[i].startDate);
                                           if (sd.year() == day.year() && sd.dayOfYear() == day.dayOfYear()) {
                                               data['hour'] = row.hours[i];
                                           }
                                       }
                                   }
                                   {% endif %}
                                   //No hours, check if day is off
                                   if (day_schedule.working_days == 0 && !data['hour']) {
                                       data['day_off'] = true;
                                   }
                               }

                               var addEventData = function (events, eventType, data) {
                                   for (var i = 0; i < events.length; i++) {
                                       var ev = events[i];
                                       var sd = moment(ev.startDate);
                                       var ed = moment(ev.endDate);

                                       if (day.isSame(sd, 'day') || day.isSame(ed, 'day') || day.isBetween(sd, ed, 'day')) {
                                           if (!data.hasOwnProperty(eventType)) {
                                               data[eventType] = {};
                                           }
                                           data[eventType][ev.id] = ev;
                                       }
                                   }

                                   return data;
                               };

                               //Check Events
                               if (row.events) {
                                   data = addEventData(row.events, 'employee-event', data);
                               }

                               if (row.company_events) {
                                   data = addEventData(row.company_events, 'company-event', data);
                               }

                               return data;
                           },
                           "render": function ( data, type, row, meta ) {
                               //var api = grid.getDataTable();
                               //var $cell = $(api.cell(meta.row, meta.col).node());
                               //$cell.addClass('event-day-cell');

                               var day = data['day'];
                               var content = '';

                               //Check Hours and Schedule
                               if (row.schedule) {
                                   var day_schedule = row.schedule[day.isoWeekday()];
                                   //Set flag to determine if user has worked
                                   {% if not tableSettings.hideHours is defined or not tableSettings.hideHours %}
                                   if (data['hour']) {
                                       $span = $('<span>').addClass('event-hour');
                                       $span.html(DigipayUtils.convertHToTime(data['hour'].hours) + ' / ' + DigipayUtils.convertHToTime(day_schedule.working_hours));
                                       content += $span.prop('outerHTML');
                                   }
                                   {% endif %}
                               }

                               var getEventCellContent = function (events, eventType) {
                                   var html = '';
                                   for (var evId in events) {
                                       var ev = events[evId];
                                       var sd = moment(ev.startDate);
                                       var ed = moment(ev.endDate);

                                       if (day.isSame(sd, 'day') || day.isSame(ed, 'day') || day.isBetween(sd, ed, 'day')) {
                                           $span = $('<span>').addClass('event-day');
                                           if(day.isSame(sd, 'day') && ev.startPM) {
                                               $span.addClass('event-halfday-right');
                                           }

                                           if(day.isSame(ed, 'day') && ev.endAM) {
                                               $span.addClass('event-halfday-left');
                                           }

                                           if(ev.status === 0) {
                                               $span.addClass('event-not-validated');
                                           }
                                           $span.addClass(eventType);
                                           $span.addClass('event-' + ev.code);
                                           $span.attr('data-event-id', ev.id);
                                           $span.attr('data-event-type', eventType);
                                           html = $span.prop('outerHTML') + html;
                                       }
                                   }

                                   return html;
                               };

                               //Check Company Events
                               if (data['company-event']) {
                                   content += getEventCellContent(data['company-event'],'company-event');
                               }
                               //Check Events
                               if (data['employee-event']) {
                                   content += getEventCellContent(data['employee-event'],'employee-event');
                               }

                               //No hours, check if day is off
                               if (content == '' && data['day_off']) {
                                   $span = $('<span>').addClass('event-day day-off');
                                   content += $span.prop('outerHTML');
                               }

                               return content;
                           }},
                       {% elseif col.render_type is defined and col.render_type == "total_variable" %}
                           {"orderable": false,
                           "searchable": false,
                           "data": function ( row, type, set ) {
                               var $span = $('<span>');
                               //$span.addClass('variable-item variable-euro');
                               $span.addClass('variable-item');
                               var total = 0.00;
                               if (row.variables) {
                                   for (var code in row.variables) {
                                       if (row.variables[code].amount !== null)
                                           total += parseFloat(row.variables[code].amount);
                                   }
                               }
                               total = DigipayUtils.moneyNumber(total);
                               $span.html(total);

                               return $span.prop('outerHTML');
                           }
                           },
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
                           {"data": "{{ col.filter_name }}"},
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
