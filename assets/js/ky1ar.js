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
          $(".ky1-hrr").empty();
  
          var daysCounter = 0;
          var $currentHrrBox;
          var currentWeekDays = []; // Array para almacenar los datos de la semana actual
  
          response.schedule.forEach(function (entry, index) {
            var dayOfWeek = entry.day_number % 7; // Obtener el día de la semana (lunes = 1, martes = 2, ..., sábado = 6)
  
            // Si el día de la semana es lunes (1), comenzar una nueva semana
            if (dayOfWeek === 1) {
              // Agregar la semana actual a la lista de semanas
              if (currentWeekDays.length > 0) {
                addWeekToSchedule(currentWeekDays);
              }
              // Limpiar los datos de la semana actual
              currentWeekDays = [];
            }
  
            // Agregar el día actual al arreglo de la semana
            currentWeekDays.push(entry);
  
            // Si hemos procesado todos los registros, añadir la semana final
            if (index === response.schedule.length - 1) {
              addWeekToSchedule(currentWeekDays);
            }
          });
  
          console.log(response.schedule);
        } else {
          console.error(response.message);
        }
      },
  
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  
    // Función para agregar una semana al calendario
    function addWeekToSchedule(weekData) {
      $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
      $("<span>Semana " + ($(".hrr-box").length + 1) + "</span>").appendTo($currentHrrBox);
      $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
  
      var $hrrDay = $currentHrrBox.find('.hrr-day');
  
      // Días de la semana ordenados por número de día
      var daysOfWeek = ['lun', 'mar', 'mié', 'jue', 'vie', 'sab'];
  
      // Recorrer los días de la semana
      for (var i = 1; i <= 6; i++) {
        var currentDayData = weekData.find(function (dayData) {
          return dayData.day_number % 7 === i;
        });
  
        var dayName;
        var dayNumber;
  
        if (currentDayData) {
          dayName = currentDayData.day_name_espanol.substring(0, 3);
          dayNumber = currentDayData.day_number;
        } else {
          dayName = daysOfWeek[i - 1];
          dayNumber = '';
        }
  
        var $dayList = $("<ul></ul>").appendTo($hrrDay);
        $("<li class='day-nam'>" + dayName + " " + dayNumber + "</li>").appendTo($dayList);
  
        if (currentDayData) {
          var stamps = currentDayData.stamp.split(",");
          stamps.forEach(function (stamp) {
            for (var j = 0; j < stamp.length; j += 5) {
              $("<li>" + stamp.slice(j, j + 5) + "</li>").appendTo($dayList);
            }
          });
        }
      }
    }
  }
  
  
