$("div.form-group select").change(function() {

               if($(this).val()==='interim'){
                   $('html, body').animate({ scrollTop: 200 }, 500);
                   $(".Interim-body,.Interim-title,#labelinterm").show(800);
                   $(".stagiere-body,.Stage-title,#labelstage").hide(800);
                   $(".form-actions button").attr('name','_interim');
                   $('this option[value=interim]').prop('selected', true);
                   $('this option[value=stage]').prop('selected', false);
               }else if($(this).val()==='stage'){
                   $('html, body').animate({ scrollTop: 200 }, 500);
                   $(".stagiere-body,.Stage-title,#labelstage").show(800);
                   $(".Interim-body,.Interim-title,#labelinterm").hide(800);
                   $(".form-actions button").attr('name','_stage');
                   $('this option[value=stage]').prop('selected', true);
                   $('this option[value=interim]').prop('selected', false);
               };
           })

           $("#add_extern_workflow_choiceoption").val()

           $("#add_extern_workflow_choiceoption").change(function() {

                          if($(this).val()==='interim'){
                              $('html, body').animate({ scrollTop: 200 }, 500);
                              $(".Interim-body,.Interim-title,#labelinterm").show(800);
                              $(".stagiere-body,.Stage-title,#labelstage").hide(800);
                              $(".form-actions button").attr('name','_interim');
                              $('this option[value=interim]').prop('selected', true);
                              $('this option[value=stage]').prop('selected', false);
                          }else if($(this).val()==='stage'){
                              $('html, body').animate({ scrollTop: 200 }, 500);
                              $(".stagiere-body,.Stage-title,#labelstage").show(800);
                              $(".Interim-body,.Interim-title,#labelinterm").hide(800);
                              $(".form-actions button").attr('name','_stage');
                              $('this option[value=stage]').prop('selected', true);
                              $('this option[value=interim]').prop('selected', false);
                          };
                      })

                      {% set label = '' %}
  {% for choice in form.choiceoption.vars.choices %}
      {% if choice.value == '0' %}
          {% set label = 'yhohohoho' %}
      {% endif %}
          {% set label = 'kikikikikiki' %}
  {% endfor %}
  <H2>{{ label }}</H2>
