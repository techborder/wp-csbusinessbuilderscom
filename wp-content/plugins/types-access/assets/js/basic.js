/*
* This is final version.
* 
* No more code added here.
* Only bugfixes.
*
*/
(function(window, $, undefined){

var wpcfAccess=wpcfAccess || {};

$(document).ready(function(){
    
    // EXPAND/COLLAPSE (NOT USED)
    $('.wpcf-access-edit-type').click(function(){
        $(this).hide().parent().find('.wpcf-access-mode').slideToggle();
    });
    $('.wpcf-access-edit-type-done').click(function(){
        $(this).parents('.wpcf-access-mode').slideToggle().parent().find('.wpcf-access-edit-type').show();
    });
    
    // TOGGLE MODES DIVS
    $('.wpcf-access-type-item .not-managed').click(function(){
        if ($(this).is(':checked')
            && $(this).parents('.wpcf-access-mode').find('.follow').is(':checked') == false) {
            wpcfAccess.EnableInputs($(this), true);
        } else {
            wpcfAccess.EnableInputs($(this), false);
        }
        
    });
    $('.wpcf-access-type-item .follow').click(function(){
        if ($(this).is(':checked') == false
            && $(this).parents('.wpcf-access-mode').find('.not-managed').is(':checked')) {
            wpcfAccess.EnableInputs($(this), true);
        } else {
            wpcfAccess.EnableInputs($(this), false);
        }
    });
    $('.wpcf-access-type-item .follow').each(function(){
        if ($(this).is(':checked') == false
            && $(this).parents('.wpcf-access-mode').find('.not-managed').is(':checked')) {
            wpcfAccess.EnableInputs($(this), true);
        } else {
            wpcfAccess.EnableInputs($(this), false);
        }
    });
    $('.wpcf-access-type-item .not-managed').each(function(){
        if ($(this).is(':checked')
            && $(this).parents('.wpcf-access-mode').find('.follow').is(':checked') == false) {
            wpcfAccess.EnableInputs($(this), true);
        } else {
            wpcfAccess.EnableInputs($(this), false);
        }
        
    });
    
    
    $('select[name^="wpcf_access_bulk_set"]').change(function(){
        var value = $(this).val();
        if (value != '0') {
            $(this).parent().find('select').each(function(){
                $(this).val(value);
            });
        }
    });
    
    // ASSIGN LEVELS
    $('#wpcf_access_admin_form').on('click', '.wpcf-access-change-level', function(){
        $(this).hide().parent().find('.wpcf-access-custom-roles-select-wrapper').slideDown();
    });
    $('#wpcf_access_admin_form').on('click', '.wpcf-access-change-level-cancel', function(){
        $(this).parent().slideUp().parent().find('.wpcf-access-change-level').show();
    });
    $('#wpcf_access_admin_form').on('click', '.wpcf-access-change-level-apply', function(){
        wpcfAccess.ApplyLevels($(this));
    });
    
    // SAVE SETTINGS
    $('#wpcf_access_admin_form').on('click', '.wpcf-access-submit', function(){
        var object = $(this);
        var img = $(this).next();
        $('#wpcf_access_admin_form').find('.dep-message').hide();
        img.css('visibility', 'visible').animate({
            opacity: 1
        }, 0);
        $.ajax({
            url: ajaxurl,
            type: 'post',
            //            dataType: 'json',
            data: $('#wpcf_access_admin_form').serialize(),
            cache: false,
            beforeSend: function() {
                object.parents('.wpcf-access-type-item').css('background-color', "#FFFF9C");
            },
            success: function(data) {
                img.animate({
                    opacity: 0
                }, 200);
                object.parents('.wpcf-access-type-item').css('background-color', "#F7F7F7");
                if (''!=data)
                {
                    $('#wpcf_access_notices').empty().html(data);
                }
            }
        });
        return false;
    });
    
    
    // NEW ROLE
    $('#wpcf-access-new-role .button').click(function(){
        $('#wpcf-access-new-role .toggle').show().find('.input').val('').focus();
        $('#wpcf-access-new-role .ajax-response').html('');
    });
    $('#wpcf-access-new-role .cancel').click(function(){
        $('#wpcf-access-new-role .confirm').attr('disabled', 'disabled');
        $('#wpcf-access-new-role .toggle').hide().find('.input').val('');
        $('#wpcf-access-new-role .ajax-response').html('');
    });
    $('#wpcf-access-new-role .confirm').click(function(){
        if ($(this).attr('disabled')) {
            return false;
        }
        $(this).attr('disabled', 'disabled');
        $('#wpcf-access-new-role .img-waiting').show();
        $('#wpcf-access-new-role .ajax-response').html('');
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: 'action=wpcf_access_add_role&role='+$('#wpcf-access-new-role .input').val(),
            cache: false,
            beforeSend: function() {},
            success: function(data) {
                $('#wpcf-access-new-role .img-waiting').hide();
                if (data.error == 'false') {
                    $('#wpcf-access-new-role .input').val('');
                    $('#wpcf-access-custom-roles-wrapper').html(data.output);
                } else {
                    $('#wpcf-access-new-role .ajax-response').html(data.output);
                }
                window.location = 'admin.php?page=wpcf-access#custom-roles';
                window.location.reload(true);
                
            }
        });
    });
    $('#wpcf-access-new-role .input').keyup(function(){
        $('#wpcf-access-new-role .ajax-response').html('');
        if ($(this).val().length > 4) {
            $('#wpcf-access-new-role .confirm').removeAttr('disabled');
        } else {
            $('#wpcf-access-new-role .confirm').attr('disabled', 'disabled');
        }
    });
    
    // DELETE ROLE
    $('#wpcf_access_admin_form').on('click', '#wpcf-access-delete-role', function() {
        $(this).next().show();
    });
    $('#wpcf_access_admin_form').on('click', '.wpcf-access-reassign-role-popup .confirm', function() {
        if ($(this).attr('disabled')) {
            return false;
        }
        $('.wpcf-access-reassign-role-popup .img-waiting').show();
        $.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: 'action=wpcf_access_delete_role&'+$(this).parents('.wpcf-access-reassign-role-popup').find(':input').serialize(),
            cache: false,
            beforeSend: function() {},
            success: function(data) {
                $('.wpcf-access-reassign-role-popup .img-waiting').hide();
                if (data.error == 'false') {
                    tb_remove();
                    $('#wpcf-access-custom-roles-wrapper').html(data.output);
                } else {
                    $('.wpcf-access-reassign-role-popup .ajax-response').html(data.output);
                }
                window.location = 'admin.php?page=wpcf-access#custom-roles';
                window.location.reload(true);
                
            }
        });
    });
    $('.wpcf-access-reassign-role-popup select').change(function(){
        $(this).parents('.wpcf-access-reassign-role-popup').find('.confirm').removeAttr('disabled');
    });
    
    // ADD DEPENDENCY MESSAGE
    $('.wpcf-access-type-item').find('.wpcf-access-mode').prepend('<div class="dep-message" style="display:none;"></div>');
    
    // Disable admin checkboxes
    $(':checkbox[value="administrator"]').attr('disabled', 'disabled').attr('readonly', 'readonly').attr('checked', 'checked');
});

wpcfAccess.Reset = function (object) {
    $('#wpcf_access_admin_form').find('.dep-message').hide();
    $.ajax({
        url: object.attr('href')+'&button_id='+object.attr('id'),
        type: 'get',
        dataType: 'json',
        //            data: ,
        cache: false,
        beforeSend: function() {},
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined' && typeof data.button_id != 'undefined') {
                    var parent = $('#'+data.button_id).parent();
                    $.each(data.output, function(index, value) {
                        object = parent.find('input[id*="_permissions_'+index+'_'+value+'_role"]');
                        object.trigger('click').attr('checked', 'checked');
                    });
                }
            }
        }
    });
    return false;
}

wpcfAccess.ApplyLevels = function (object) {
    $.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: object.parent().find('.wpcf-access-custom-roles-select').serialize()+'&_wpnonce='+wpcf_nonce_ajax_callback+'&action=wpcf_access_ajax_set_level',
        cache: false,
        beforeSend: function() {
            $('#wpcf-access-custom-roles-table-wrapper').css('opacity', 0.5);
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined') {
                    //                    $('#wpcf-access-custom-roles-wrapper').css('opacity', 1).replaceWith(data.output);
                    window.location = 'admin.php?page=wpcf-access#custom-roles';
                    window.location.reload(true);
                }
            }
        }
    });
    return false;
}

wpcfAccess.Enable = function (object) {
    if ((object.is('input[type="checkbox"]') && object.is(':checked')) || (object.is('input[type="radio"]') && object.val() != 'not_managed')) {
        wpcfAccess.EnableInputs(object, true);
    } else {
        wpcfAccess.EnableInputs(object, false);
    }
}

wpcfAccess.EnableInputs = function (object, check) {
    if (check) {
        object.parent().find('.wpcf-enable-set').val(object.val());
        object.parent().parent().parent().find('table input, .wpcf-access-submit, .wpcf-access-reset').not(':checkbox[value="administrator"]').removeAttr('readonly').removeAttr('disabled');
        object.parent().parent().parent().find('.warning-fallback').hide();
    } else {
        object.parent().find('.wpcf-enable-set').val('not_managed');
        object.parent().parent().parent().find('table input, .wpcf-access-reset').attr('readonly', 'readonly').attr('disabled', 'disabled');
        object.parent().parent().parent().find('.warning-fallback').show();
    }
}

wpcfAccess.AutoThick = function (object, cap, name) {
    var thick = new Array();
    var thickOff = new Array();
    var active = object.is(':checked');
    var role = object.val();
    var cap_active = 'wpcf_access_dep_true_'+cap;
    var cap_inactive = 'wpcf_access_dep_false_'+cap;
    var message = new Array();
    
    if (active) {
        if (typeof window[cap_active] != 'undefined') {
            thick = thick.concat(window[cap_active]);
        }
    } else {
        if (typeof window[cap_inactive] != 'undefined') {
            thickOff = thickOff.concat(window[cap_inactive]);
        }
    }
    
    // FIND DEPENDABLES
    //
    // Check ONs
    $.each(thick, function(index, value){
        object.parents('table').find(':checkbox').each(function(){
            if ($(this).attr('id') != object.attr('id')) {
                if ($(this).val() == role
                    && $(this).hasClass('wpcf-access-'+value)) {
                    // Mark for message
                    if ($(this).is(':checked') == false) {
                        message.push($(this).data('wpcfaccesscap'));
                    }
                    // Set element form name
                    $(this).attr('checked', 'checked')
                    .attr('name', $(this).data('wpcfaccessname'));
                    wpcfAccess.ThickTd($(this), 'prev', true);
                }
            }
        });
    });
    // Check OFFs
    $.each(thickOff, function(index, value){
        object.parents('table').find(':checkbox').each(function(){
            if ($(this).attr('id') != object.attr('id')) {
                if ($(this).val() == role
                    && $(this).hasClass('wpcf-access-'+value)) {
                    // Mark for message
                    if ($(this).is(':checked')) {
                        message.push($(this).data('wpcfaccesscap'));
                    }
                    $(this).removeAttr('checked').attr('name', 'dummy');
                    // Set element form name
                    var prevSet = $(this).parent().prev().find(':checkbox');
                    if (prevSet.is(':checked')) {
                        prevSet.attr('checked', 'checked').attr('name', prevSet.data('wpcfaccessname'));
                    }
                    wpcfAccess.ThickTd($(this), 'next', false);
                }
            }
        });
    });
    
    // Thick all checkboxes
    wpcfAccess.ThickTd(object, 'next', false);
    wpcfAccess.ThickTd(object, 'prev', true);
    
    // SET NAME
    // 
    // Find previous if switched off
    if (object.is(':checked')) {
        object.attr('name', name);
    } else {
        object.attr('name', 'dummy');
        object.parent().prev().find(':checkbox').attr('checked', 'checked').attr('name', name);
    }
    // Set true if admnistrator
    if (object.val() == 'administrator') {
        object.attr('name', name).attr('checked', 'checked');
    }
    
    // Alert
    wpcfAccess.DependencyMessageShow(object, cap, message, active);
}

wpcfAccess.ThickTd = function (object, direction, checked) {
    if (direction == 'next') {
        var cbs = object.parent().nextAll('td').find(':checkbox');
    } else {
        var cbs = object.parent().prevAll('td').find(':checkbox');
    }
    
    if (checked) {
        cbs.each(function(){
            $(this).attr('checked', 'checked').attr('name', 'dummy');
        });
    } else {
        cbs.each(function(){
            $(this).removeAttr('checked').attr('name', 'dummy');
        });
    }
}

wpcfAccess.DependencyMessageShow = function (object, cap, caps, active) {
    var update_message = wpcfAccess.DependencyMessage(cap, caps, active);
    var update = object.parents('.wpcf-access-type-item').find('.dep-message');
    update.hide().html('');
    if (update_message != false) {
        update.html(update_message).show();
    }
}

wpcfAccess.DependencyMessage = function (cap, caps, active) {
    var active_pattern_singular = window['wpcf_access_dep_active_messages_pattern_singular'];
    var active_pattern_plural = window['wpcf_access_dep_active_messages_pattern_plural'];
    var inactive_pattern_singular = window['wpcf_access_dep_inactive_messages_pattern_singular'];
    var inactive_pattern_plural = window['wpcf_access_dep_inactive_messages_pattern_singular'];
    /*var no_edit_comments = window['wpcf_access_edit_comments_inactive'];*/
    var caps_titles = new Array();
    var update_message = false;
    
    $.each(caps, function(index, value){
        if (active) {
            var key = window['wpcf_access_dep_true_'+cap].indexOf(value);
            caps_titles.push(window['wpcf_access_dep_true_'+cap+'_message'][key]);
        } else {
            var key = window['wpcf_access_dep_false_'+cap].indexOf(value);
            caps_titles.push(window['wpcf_access_dep_false_'+cap+'_message'][key]);
        }
    });

    if (caps.length > 0) {
        if (active) {
            if (caps.length < 2) {
                var update_message = active_pattern_singular.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            } else {
                var update_message = active_pattern_plural.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            }
        } else {
            if (caps.length < 2) {
                var update_message = inactive_pattern_singular.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            } else {
                var update_message = inactive_pattern_plural.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            }
        }
        update_message = update_message.replace('%dcaps', caps_titles.join('\', \''));
    }
    return update_message;
}
// export it
window.wpcfAccess=window.wpcfAccess || {};
$.extend(window.wpcfAccess, wpcfAccess);
})(window, jQuery);