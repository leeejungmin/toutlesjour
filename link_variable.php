{{ datatable.renderJs(tableSettings) }}
    {{ parent() }}
    <script>
        jQuery(document).ready(function() {
            if(localStorage.getItem('planningShowHideFiltersFlag')!= undefined){
                if(localStorage.getItem('planningShowHideFiltersFlag') =="yes"){
                    $('.table-container .table-filter-wrapper ,#hide_show_layout').hide(800);
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
                $('.table-container .table-filter-wrapper ,#hide_show_layout').toggle(800);
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
            var table = $('#variable_data').DataTable();
            var closure = {{ closureStatus }};
            //Variable Modification Access [always available for Admin] for DRH and USER (active only in the the open period)
            {% if userCanImportVariable is defined and userCanImportVariable is not null %}
                table.on( 'user-select', function ( e, dt, type, cell, originalEvent ) {
                    var data = cell.data();
                    if (data && typeof data == 'object' && 'code' in data) {
                        var url = "{{ path('app_company_variable_modaledit', {'company':company.id})}}/"
                            + data['employee_id'] + '/' + data['code'] + "/{{ start|date('Y-m-d') }}";
                        $('body').popinify({'targetUrl' : url});
                    }
                    return false;
                });
            {% endif %}
            $('body').on('formSucess.dp.popinify', function() {
                table.ajax.reload();
            });

            //Change fontSize from the menu list
            $('#text-resizing').change(function() {
                document.getElementById("datatable_container").className = "variable-page-container "+$("#text-resizing").val();
                //Save choice in the session
                localStorage.setItem('variablesfontchangesize', $("#text-resizing").val());
            });

            //After load of the page we load the last font size choosed
            if(localStorage.getItem("variablesfontchangesize") != null){
                $('#text-resizing option[value="'+localStorage.getItem("variablesfontchangesize")+'"]').attr('selected', 'selected');
                $("#text-resizing").change();
            }

            $( "#left-square").click(function() {
                $("#right-square").show('slow');
                $("#right-square").css('float','left');
                $("#left-square").hide('slow');
                $(".page-sidebar-menu").hide();
                $(".page-content").css('margin-left','1px');
            });
            $( "#right-square").click(function() {
                $("#left-square").show('slow');

                $("#right-square").hide('slow');
                $(".page-sidebar-menu").show('slow');
                $(".page-content").css('margin-left','235px');
            });

            $("#left-square").show();

            if(document.getElementById("before_array_extra_actions")){
                //Move the text resizing element to another container (before the table) to perform the UX
                $('#before_array_extra_actions').append( $('#before_array_extra_actions_content') );
            }
        });
    </script>
