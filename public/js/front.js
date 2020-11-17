jQuery(function($) {
    $('.jet-smart-listing__more, .jet-smart-listing__post-title > a').click(function(e) {
        e.preventDefault();
        showLoader();
        url = $(this).attr('href');
        _that = $(this);
        $.get('/wp-admin/admin-ajax.php?action=get_ajax_post&page=' + encodeURIComponent(url), function(r) {
            if(r.success && r.content) {
                _that.parents('.jet-smart-listing__post-content').find('.jet-smart-listing__post-excerpt').html(r.content);
                _that.parents('.jet-smart-listing__post-content').find('.jet-smart-listing__more-wrap').hide();
            }
            hideLoader();
        }, 'json');
        return false;
    });

    function showLoader() {
        html = ' <div id="loading"> <style> #overlay{	position: fixed; top: 0; z-index: 100; width: 100%; height:100%; display: block; background: rgba(0,0,0,0.6); } .cv-spinner { height: 100%; display: flex; justify-content: center; align-items: center;  } .spinner { width: 40px; height: 40px; border: 4px #ddd solid; border-top: 4px #2e93e6 solid; border-radius: 50%; animation: sp-anime 0.8s infinite linear; } @keyframes sp-anime { 100% { transform: rotate(360deg); } } </style> <div id="overlay"> <div class="cv-spinner"> <span class="spinner"></span> </div> </div> </div> ';
        $('body').append(html);
    }

    function hideLoader() {
        $('#loading').remove();
    }
});
