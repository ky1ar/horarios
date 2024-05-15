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
        console.log(response.schedule);
        if (response.success) {
          $(".ky1-hrr").empty();
          var daysOfWeek = ['lun', 'mar', 'mie', 'jue', 'vie', 'sab']; // Array de nombres de los días de la semana
  
          // Iterar sobre los datos para construir las semanas
          var weeks = [];
          response.schedule.forEach(function (entry, index) {
            var dayIndex = (entry.day_number - 1) % 7; // Obtener el índice del día en la semana
            var weekIndex = Math.floor(index / 6); // Obtener el índice de la semana
            if (!weeks[weekIndex]) weeks[weekIndex] = []; // Inicializar la semana si es la primera vez que se encuentra
            weeks[weekIndex][dayIndex] = entry; // Agregar el dato al día correspondiente
          });
  
          // Iterar sobre las semanas para construir la estructura HTML
          weeks.forEach(function (week, weekIndex) {
            var $currentHrrBox = $("<li class='hrr-box'></li>").appendTo(".ky1-hrr");
            $("<span>Semana " + (weekIndex + 1) + "</span>").appendTo($currentHrrBox);
            var $hrrDay = $("<div class='hrr-day'></div>").appendTo($currentHrrBox);
  
            // Iterar sobre los días de la semana actual
            daysOfWeek.forEach(function (dayName, dayIndex) {
              var dayData = week[dayIndex] || { day_name_espanol: '', day_number: '' }; // Obtener los datos del día o un objeto vacío si no hay datos
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
  
  
