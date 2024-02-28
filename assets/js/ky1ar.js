

$( document ).ready(function() {
  
    var usr_prv = $('#usr-prv');
    var usr_nxt = $('#usr-nxt');
    /*var usr_dta = $('#usr-dta');
    
    $('#ky1-rgt .usr-lst li').on('click', function() {

        id = $(this).data('id');
        name = $(this).data('name');
        slug = $(this).data('slug');
        area = $(this).data('area');

        usr_dta.find('.usr-nme').text(name);
        usr_dta.find('.usr-are').text(area);
        usr_dta.find('.usr-slg').attr('src', 'assets/img/' + slug + '.png');
        usr_dta.attr('data-id', id);

    });
*/
 usr_nxt.on('click', function(e) {
        var currentIndex = $('#ky1-rgt .usr-lst li').index($('#ky1-rgt .usr-lst li.active'));
        console.log('Current Index:', currentIndex);

        var totalUsers = $('#ky1-rgt .usr-lst li').length;

        // Obtener el próximo índice
        var nextIndex = (currentIndex + 1) % totalUsers;
        console.log('Next Index:', nextIndex);

        var nextUser = $('#ky1-rgt .usr-lst li').eq(nextIndex);
        var user = nextUser.data('id');
        console.log('Next User ID:', user);

        $('#ky1-rgt .usr-lst li').removeClass('active');
        nextUser.addClass('active');

        $('#usr-dta').attr('data-id', user);
    });
    
});


