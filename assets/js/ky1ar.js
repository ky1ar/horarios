// Definir la función getUserSchedule como una función global
function getUserSchedule(userId) {
    $.ajax({
        url: '../routes/del/get_user_schedule.php', 
        method: 'POST',
        data: { userId: userId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log(response.schedule);
            } else {
                console.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud AJAX:', error);
        }
    });
}

$(document).ready(function() {
    const nextUser = $('#nextUser');
    const previousUser = $('#previousUser');
    const userList = $('#userList');

    const selectedUser = $('#selectedUser');
    const userName = $('#userName');
    const userCategory = $('#userCategory');

    const userImage = $('#userImage');
    const imagePath = 'assets/img/profiles/';

    function updateUser(offset) {
        let current = userList.find('.active').index();
        let total = userList.find('li').length - 1;
        userList.find('li').removeClass('active');

        current = current + offset;
        if (offset == 1) {
            if (current > total) current = 0;
        } else if (offset == -1) {
            if (current < 0) current = total;
        }

        let newUser = userList.find('li').eq(current);
        newUser.addClass('active');

        selectedUser.attr('data-id', newUser.data('id'));
        userImage.attr('src', imagePath + newUser.data('slug') + '.png');
        userName.text(newUser.data('name'));
        userCategory.text(newUser.data('category'));
        
        // Llamar a la función getUserSchedule cuando se actualiza el usuario
        getUserSchedule(newUser.data('id'));  
    }

    // Evento de clic para el botón "Siguiente Usuario"
    nextUser.on('click', function() { updateUser(1); });  
    
    // Evento de clic para el botón "Usuario Anterior"
    previousUser.on('click', function() { updateUser(-1); });

    // Evento de clic para los elementos de la lista de usuarios
    userList.find('li').on('click', function() { 
        let $this = $(this);
        userList.find('li').removeClass('active');
        $this.addClass('active');

        selectedUser.attr('data-id', $this.data('id'));
        userImage.attr('src', imagePath + $this.data('slug') + '.png');
        userName.text($this.data('name'));
        userCategory.text($this.data('category'));

        // Llamar a la función getUserSchedule cuando se hace clic en un usuario
        getUserSchedule($this.data('id'));  
    });
});
