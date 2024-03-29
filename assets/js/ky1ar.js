

$( document ).ready(function() {
  
    
    
    /*var usr_dta = $('#selectedUser');
    
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
    const nextUser = $('#nextUser');
    const previousUser = $('#previousUser');
    const userList = $('#userList');

    const selectedUser = $('#selectedUser');
    const userName = $('#userName');
    const userCategory = $('#userCategory');

    const userImage = $('#userImage');
    const imagePath = 'assets/img/';
    
    function updateUser(offset){

        let current = userList.find('.active').index();
        let total = userList.find('li').length - 1;
        userList.find('li').removeClass('active');

        current = current + offset;
        if (offset == 1) {
            if(current>total) current = 0;
        } else if (offset == -1) {
            if(current<0) current = total;
        }

        let newUser = userList.find('li').eq(current);
        newUser.addClass('active');

        selectedUser.attr('data-id',newUser.data('id'));
        userImage.attr('src', imagePath + newUser.data('slug') + '.png');
        userName.text(newUser.data('name'));
        userCategory.text(newUser.data('category'));
    }

    nextUser.on('click', function() { updateUser(1); });  
    previousUser.on('click', function() { updateUser(-1); });

    userList.find('li').on('click', function() { 

        let $this = $(this);
        userList.find('li').removeClass('active');
        $this.addClass('active');

        selectedUser.attr('data-id',$this.data('id'));
        userImage.attr('src', imagePath + $this.data('slug') + '.png');
        userName.text($this.data('name'));
        userCategory.text($this.data('category'));
    });

});


