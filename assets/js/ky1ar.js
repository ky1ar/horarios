$(document).ready(function () {
  const nextUser = $("#nextUser");
  const previousUser = $("#previousUser");
  const userList = $("#userList");

  const selectedUser = $("#selectedUser");
  const userName = $("#userName");
  const userCategory = $("#userCategory");

  const userImage = $("#userImage");
  const imagePath = "assets/img/profiles/";

  function updateUser(offset) {
    let current = userList.find(".active").index();
    let total = userList.find("li").length - 1;
    userList.find("li").removeClass("active");

    current = current + offset;
    if (offset == 1) {
      if (current > total) current = 0;
    } else if (offset == -1) {
      if (current < 0) current = total;
    }

    let newUser = userList.find("li").eq(current);
    newUser.addClass("active");

    selectedUser.attr("data-id", newUser.data("id"));
    userImage.attr("src", imagePath + newUser.data("slug") + ".png");
    userName.text(newUser.data("name"));
    userCategory.text(newUser.data("category"));

    getUserSchedule(newUser.data("id"));
  }

  nextUser.on("click", function () {
    updateUser(1);
  });
  previousUser.on("click", function () {
    updateUser(-1);
  });

  userList.find("li").on("click", function () {
    let $this = $(this);
    userList.find("li").removeClass("active");
    $this.addClass("active");

    selectedUser.attr("data-id", $this.data("id"));
    userImage.attr("src", imagePath + $this.data("slug") + ".png");
    userName.text($this.data("name"));
    userCategory.text($this.data("category"));

    getUserSchedule($this.data("id"));
  });
});

function getUserSchedule(userId) {
    $.ajax({
      url: "../routes/del/get_user_schedule.php",
      method: "POST",
      data: { userId: userId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // Limpiar contenido existente
          $(".hrr-day").empty();
  
          // Recorrer los datos del horario del usuario
          response.schedule.forEach(function (entry) {
            var $dayList = $("<ul></ul>").appendTo(".hrr-day");
            var dayName = entry.day_name; // Supongamos que hay un campo que indica el nombre del día
            var dateNumber = entry.date_number; // Supongamos que hay un campo que indica el número de la fecha
  
            // Formatear el nombre del día junto con el número de la fecha
            var formattedDayName = getFormattedDayName(dayName, dateNumber);
  
            // Agregar el nombre del día junto con el número de la fecha como primer elemento de la lista
            $("<li class='day-nam'>" + formattedDayName + "</li>").appendTo($dayList);
  
            // Dividir el sello de tiempo en intervalos y agregarlos como elementos de la lista
            var stamps = entry.stamp.split(",");
            stamps.forEach(function (stamp) {
              $("<li>" + stamp + "</li>").appendTo($dayList);
            });
          });
        } else {
          console.error(response.message);
        }
      },
  
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  }
  
  // Función para formatear el nombre del día y el número de la fecha
  function getFormattedDayName(dayName, dateNumber) {
    // Definir un objeto para mapear los nombres de los días abreviados en español
    var dayNames = {
      "lun": "lunes",
      "mar": "martes",
      "mie": "miércoles",
      "jue": "jueves",
      "vie": "viernes",
      "sab": "sábado",
      "dom": "domingo"
    };
  
    // Obtener el nombre completo del día basado en el nombre abreviado proporcionado
    var fullDayName = dayNames[dayName.toLowerCase()];
  
    // Devolver el nombre completo del día junto con el número de la fecha
    return fullDayName + " " + dateNumber;
  }
  