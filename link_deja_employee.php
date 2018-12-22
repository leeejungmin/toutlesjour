{{ datatable.renderJs(tableSettings) }}
    {{ parent() }}
    <script>
        jQuery(document).ready(function() {
            if(localStorage.getItem('planningShowHideFiltersFlag')!= undefined){
                if(localStorage.getItem('planningShowHideFiltersFlag') =="yes"){
                    $('.table-container .table-filter-wrapper ').hide(800);
                    $('#show_hide_filter_button').html('{{'show_filter'|trans }}');
                }
            }

            //Show and hide Filters
            $('#show_hide_filter_button').click(function(){
                if($('.table-filter-wrapper').is(":visible") === true){
                    localStorage.setItem('planningShowHideFiltersFlag', 'yes');
                    $('#show_hide_filter_button').html('{{'show_filter'|trans }}');
                }else{
                    localStorage.setItem('planningShowHideFiltersFlag', '');
                    $('#show_hide_filter_button').html('{{'hide_filter'|trans }}');
                }
                $('.table-container .table-filter-wrapper ').toggle(800);
            });

            {% if importResultsLog is defined and importResultsLog is not empty  %}
            $("#imports_result_show_result_btn_container").show("slow");
            {% endif %}

            $("#imports_result_show_details").click(function() {
                if($("#imports_result_container").css('display')=="none"){
                    $("#imports_result_container").show("slow");
                    $("#imports_result_show_details").text('{{ "button.show.value.hide.all.details"|trans }}');
                }else{
                    $("#imports_result_container").hide("slow");
                    $("#imports_result_show_details").text('{{ "button.show.value.show.all.details"|trans }}');
                }
            });

            //if($('#custom_field').is(':checked')){
            //}

            //storing date export in the hidden value
            $('#datepicker_start').datepicker().on("input change", function (e) {
                //la date selectionné apartir de date picker
                    var content_start_date = e.target.value;
                    document.getElementById('element_content_start').value = content_start_date;
                });
            $('#datepicker_end').datepicker().on("input change", function (e) {
                    //la date selectionné apartir de date picker
                    var content_end_date = e.target.value;
                    document.getElementById('element_content_end').value = content_end_date;
            });
            //Action to submit the form containing the export date value
            $( "#export_employees" ).click(function() {
                document.getElementById("actual_value_of_list").submit();
            });
            //hidinding and showing date fiels when radio box is shecked
            if (jQuery('input[value=custom_field]:checked').length > 0){
                jQuery('#period_field_picker').show()
            }
            else {
                jQuery('#period_field_picker').hide();
            }
            $('input[name=optradio]').change(function() {
                var selected = jQuery(this).val();
                if(selected == 'custom_field'){
                    document.getElementById('element_flag').value = "1";
                    document.getElementById('datepicker_start').value = "";
                    document.getElementById('datepicker_end').value = "";
                    jQuery('#period_field_picker').show();
                } else {
                    jQuery('#period_field_picker').hide();
                }
            });
            $('input[id="all_salarial"]').on('change', function(){
                document.getElementById('element_flag').value = "null";
                document.getElementById('element_content_start').value = "null";
                document.getElementById('element_content_end').value = "null";
            });
            //injectin the first day and the last day of the actual month
            $('input[id="active_month"]').on('change', function(){
                document.getElementById('element_flag').value = "null";
                var first_day = new Date();
                var dd = first_day.getDate();
                var mm = first_day.getMonth()+1; //January is 0!

                var yyyy = first_day.getFullYear();
                if(dd < 10){
                    dd='0'+dd;
                }
                if(mm < 10){
                    mm="0"+mm;
                }
                first_day = yyyy+'-'+mm+'-'+'01';
                var date = new Date();
                var format_last_day = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                var last_day = format_last_day.getFullYear() + '-' + ("0" + (format_last_day.getMonth() + 1)).slice(-2) + '-' + (format_last_day.getDate());
                document.getElementById('element_content_start').value = first_day;
                document.getElementById('element_content_end').value = last_day ;
            });
        });
    </script>
{% endblock %}
