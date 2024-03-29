$(document).ready(function () {
  const searchOrderMessage = $("#searchOrderMessage");
  const searchOrder = $("#searchOrder");

  const calendarPrev = $("#calendarPrev");
  const calendarNext = $("#calendarNext");
  const calendarTable = $("#calendarTable");
  const monthName = $("#monthName");
  const loadingResponse = $("#loadingResponse");

  const machine = $("#machine");
  const suggestions = $("#suggestions");
  const machineImage = $("#machineImage");
  const machineId = $("#machineId");

  const selectedSchedule = $("#selectedSchedule");
  const picked = $("#picked");

  const scheduleSubmit = $("#scheduleSubmit");
  const scheduleFormMessage = $("#scheduleFormMessage");
  const scheduleCalendar = $("#scheduleCalendar");

  function message(target, message) {
    target.text(message).slideDown();
  }

  searchOrder.submit(function (event) {
    event.preventDefault();
    searchOrderMessage.slideUp();

    var orderNumber = $("#orderNumber").val();
    var document = $("#document").val();

    if (!validateorderNumber(orderNumber) || !validateDocument(document)) {
      return;
    }

    $.ajax({
      type: "POST",
      url: "routes/searchOrder",
      data: {
        orderNumber: orderNumber,
        document: document,
      },
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success) {
          window.location.href = "order?number=" + orderNumber;
        } else {
          message(searchOrderMessage, jsonData.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });

  var currentDate = new Date();
  var today = new Date();
  var months = [
    "enero",
    "febrero",
    "marzo",
    "abril",
    "mayo",
    "junio",
    "julio",
    "agosto",
    "septiembre",
    "octubre",
    "noviembre",
    "diciembre",
  ];

  calendarPrev.click(function () {
    var offsetMonth = currentDate.getMonth() - 1;
    if (offsetMonth > today.getMonth()) {
      loadCalendar(-1);
      calendarNext.removeClass("disabled");
    } else if (offsetMonth == today.getMonth()) {
      loadCalendar(-1);
      $(this).addClass("disabled");
    }
  });

  calendarNext.click(function () {
    var offsetMonth = currentDate.getMonth() + 1;
    var maxMonth = today.getMonth() + 2;
    if (offsetMonth < maxMonth) {
      loadCalendar(1);
      calendarPrev.removeClass("disabled");
    } else if (offsetMonth == maxMonth) {
      loadCalendar(1);
      $(this).addClass("disabled");
    }
  });

  const calendarNavigation = $("#calendarNavigation");
  const calendarBackDiv = $("#calendarBackDiv");
  const calendarBack = $("#calendarBack");
  const calendarSelector = $("#calendarSelector");
  const scheduleSelector = $("#scheduleSelector");
  const scheduleForm = $("#scheduleForm");

  calendarBack.click(function () {
    scheduleSelector.hide();
    calendarBackDiv.hide();
    scheduleForm.hide();

    calendarSelector.show();
    calendarNavigation.show();
    loadCalendar(0);
    calendarPrev.addClass("disabled");
    calendarNext.removeClass("disabled");
  });

  function loadCalendar(offset) {
    loadingResponse.show();
    currentDate.setMonth(
      offset === 0 ? today.getMonth() : currentDate.getMonth() + offset
    );
    currentDate.setDate(1);

    var formatedDate =
      currentDate.getFullYear() +
      "-" +
      ("0" + (currentDate.getMonth() + 1)).slice(-2) +
      "-" +
      ("0" + currentDate.getDate()).slice(-2);
    var splitDate = formatedDate.split("-");
    var month = splitDate[1];
    var month = months[parseInt(month, 10) - 1];
    var firstDayNum = currentDate.getDay();
    //console.log(firstDayNum);
    $.ajax({
      url: "routes/loadCalendar",
      method: "POST",
      data: { date: formatedDate, day: firstDayNum },
      success: function (response) {
        calendarTable.html(response);
        monthName.text(month + " " + currentDate.getFullYear());
        loadingResponse.hide();
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
        loadingResponse.hide();
      },
    });
  }

  $(document).on("click", ".boxDay", function () {
    loadingResponse.show();
    calendarSelector.hide();
    scheduleSelector.show();

    var dayNumber = $(this).attr("data-day");
    var temporal = currentDate;
    temporal.setDate(dayNumber);
    var formatedDate =
      temporal.getFullYear() +
      "-" +
      ("0" + (temporal.getMonth() + 1)).slice(-2) +
      "-" +
      ("0" + temporal.getDate()).slice(-2);

    $.ajax({
      url: "routes/loadSchedule",
      method: "POST",
      data: { date: formatedDate },
      success: function (response) {
        var jsonData = JSON.parse(response);
        scheduleSelector.html(jsonData.html);
        calendarNavigation.hide();
        calendarBackDiv.show();
        loadingResponse.hide();
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
        loadingResponse.hide();
      },
    });
  });

  $(document).on("click", ".boxSchedule", function () {
    const selectedData = $("#selectedData");
    const dateAvailable = $("#dateAvailable");
    let day = selectedData.data("day");
    let date = selectedData.data("date");
    let count = dateAvailable.val();
    let schedule = $(this).data("schedule");

    selectedSchedule.text(day + " - " + schedule);
    picked.val(date);
    picked.attr("data-schedule", schedule);
    picked.attr("data-count", count);

    scheduleSelector.hide();
    scheduleForm.show();
  });

  machine.keyup(function () {
    let machineVal = $(this).val();
    if (machineVal.length >= 2) {
      $.ajax({
        url: "routes/loadMachine",
        type: "POST",
        data: { machineVal: machineVal },
        success: function (data) {
          suggestions.html(data);
          $(".suggestionsRow").click(function () {
            let sel = $(this).text();
            let id = $(this).data("id");
            let slug = $(this).data("slug");
            machine.val(sel);
            suggestions.html("");
            machineImage.attr("src", "assets/mac/" + slug + ".webp");
            machineId.val(id);
          });
        },
      });
    } else {
      suggestions.html("");
      machineImage.attr("src", "assets/img/def.webp");
      machineId.val("");
    }
  });

  scheduleSubmit.submit(function (event) {
    event.preventDefault();
    scheduleFormMessage.slideUp();

    let date = picked.val();
    let schedule = picked.data("schedule");
    let count = picked.data("count");

    let dniRUC = $("#dniRUC").val();
    let client = $("#client").val();
    let email = $("#email").val();
    let phone = $("#phone").val();
    let machine = machineId.val();
    let invoice = $("#invoice")[0].files[0];

    if (
      !validateDniRuc(dniRUC) ||
      !validateClient(client) ||
      !validateEmail(email) ||
      !validatePhone(phone) ||
      !validateMachineId(machine) ||
      !validateInvoice(invoice)
    ) {
      return;
    }

    let formData = new FormData();
    formData.append("schedule", schedule);
    formData.append("date", date);
    formData.append("count", count);
    formData.append("dniRUC", dniRUC);
    formData.append("client", client);
    formData.append("email", email);
    formData.append("phone", phone);
    formData.append("machineId", machine);
    formData.append("invoice", invoice);

    $.ajax({
      url: "routes/registerSchedule",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        var jsonData = JSON.parse(response);
        if (jsonData.success) {
          scheduleCalendar.html(jsonData.success);
        } else {
          message(scheduleFormMessage, jsonData.error);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });

  /* Validations */
  function validateDniRuc(dniRUC) {
    if (dniRUC.trim() === "") {
      message(scheduleFormMessage, "Ingrese un documento válido");
      return false;
    }
    return true;
  }
  function validateClient(client) {
    if (client.trim() === "") {
      message(scheduleFormMessage, "El campo del nombre no puede estar vacío");
      return false;
    }
    return true;
  }
  function validateClient(client) {
    if (client.trim() === "") {
      message(scheduleFormMessage, "El campo del nombre no puede estar vacío");
      return false;
    }
    return true;
  }
  function validateEmail(email) {
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      message(scheduleFormMessage, "Ingrese un correo electrónico válido");
      return false;
    }
    return true;
  }
  function validatePhone(phone) {
    phone = phone.trim();
    var telefonoRegex = /^(\+\d+)?\d+$/;
    if (!telefonoRegex.test(phone)) {
      message(scheduleFormMessage, "Ingrese un número de teléfono válido");
      return false;
    }
    return true;
  }
  function validateMachineId(machineId) {
    if (machineId.trim() === "") {
      message(scheduleFormMessage, "Seleccione un equipo");
      return false;
    }
    return true;
  }
  function validateInvoice(invoice) {
    if (!invoice) {
      message(scheduleFormMessage, "Seleccione un archivo");
      return false;
    }
    const maxSizeInBytes = 10 * 1024 * 1024; // 10 MB
    if (invoice.size > maxSizeInBytes) {
        message(scheduleFormMessage, "El tamaño del archivo es demasiado grande");
        return false;
    }
    
    let fileType = invoice.type;
    if (fileType !== "application/pdf" && !fileType.startsWith("image/")) {
      message(scheduleFormMessage, "El archivo debe ser PDF o una imagen");
      return false;
    }
    return true;
  }
  function validateDocument(document) {
    if (
      !(document.length === 8 || document.length === 11) ||
      !/^\d+$/.test(document)
    ) {
      message(searchOrderMessage, "Ingrese un documento válido");
      return false;
    }
    return true;
  }
  function validateorderNumber(orderNumber) {
    if (orderNumber.trim() === "" || !/^\d+$/.test(orderNumber)) {
      message(searchOrderMessage, "Ingrese un número de orden válido");
      return false;
    }
    return true;
  }
});
