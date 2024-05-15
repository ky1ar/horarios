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
  
          // Array de nombres de los días de la semana
          var daysOfWeek = ['lun', 'mar', 'mie', 'jue', 'vie', 'sab'];
  
          // Obtener el primer día de la semana
          var firstDay = response.schedule[0].day_number % 7;
          var firstDayIndex = daysOfWeek.indexOf(firstDay);
  
          // Iterar desde el primer día de la semana hasta el último
          for (var i = 0; i < 6; i++) {
            var currentDayIndex = (firstDayIndex + i) % 7;
            var currentDayName = daysOfWeek[currentDayIndex];
  
            // Buscar si hay datos para este día en la respuesta AJAX
            var currentDayData = response.schedule.find(function (entry) {
              return entry.day_number % 7 === currentDayIndex + 1;
            });
  
            // Si no hay datos para este día, añadir un objeto vacío
            if (!currentDayData) {
              currentDayData = { day_name_espanol: '', day_number: '' };
            }
  
            // Agregar el día a la lista
            addDayToList(currentDayName, currentDayData);
          }
  
          console.log(response.schedule);
        } else {
          console.error(response.message);
        }
      },
  
      error: function (xhr, status, error) {
        console.error("Error en la solicitud AJAX:", error);
      },
    });
  
    // Función para agregar un día a la lista
    function addDayToList(dayName, dayData) {
      var $currentHrrBox;
      var $hrrDay;
  
      // Si es el primer día de la semana, crear un nuevo contenedor hrr-box
      if (dayName === 'lun') {
        $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
        $("<span>Semana " + ($(".hrr-box").length + 1) + "</span>").appendTo($currentHrrBox);
        $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
        $hrrDay = $currentHrrBox.find('.hrr-day');
      } else {
        // Si no es el primer día de la semana, obtener el contenedor hrr-day del último hrr-box
        $hrrDay = $(".ky1-hrr").find('.hrr-day').last();
      }
  
      var $dayList = $("<ul></ul>").appendTo($hrrDay);
      $("<li class='day-nam'>" + dayName + " " + dayData.day_number + "</li>").appendTo($dayList);
  
      // Agregar las horas correspondientes si hay datos para este día
      if (dayData.stamp) {
        var stamps = dayData.stamp.split(",");
        stamps.forEach(function (stamp) {
          for (var i = 0; i < stamp.length; i += 5) {
            $("<li>" + stamp.slice(i, i + 5) + "</li>").appendTo($dayList);
          }
        });
      }
    }
  }
  
