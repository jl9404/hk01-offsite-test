<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Application') }}</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <style>
        body {
            background-color: #FAFAFA;
        }
        .input-group-addon > select {
            background-color: transparent;
            border: none;
        }
    </style>
</head>
<body>


    <nav class="navbar navbar-toggleable-md navbar-light bg-faded">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="container">
        <a class="navbar-brand" href="/">{{ config('app.name', 'Application') }}</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/">Make Payment</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/query">Check Payment Record</a>
                </li>
            </ul>
        </div>
        </div>
    </nav>


@yield('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script>
$(function() {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });



  function renderErrs(json, target)
  {
    var tpl = '<div class="alert alert-danger"><ul>';
    for (field in json) {
      for (error in json[field]) {
        tpl += '<li>' + json[field][error] + '</li>';
      }
    }
    tpl = tpl + '</ul></li>';
    target.find('div.alert-danger').remove();
    target.prepend(tpl);
  }

  function errHandler(xhr, $form)
  {
    if (xhr.status === 422) {
      renderErrs(xhr.responseJSON, $form);
    } else if(xhr.status === 400) {
      swal("Oops...", "Something went wrong...", "error");
    } else if (xhr.status === 404) {
      swal("Oops...", "Record is not existed.", "error");
    } else if (xhr.status ===  429) {
      swal("Oops...", "Too many attempts.", "error");
    }
  }

  function inputToggle(form) {
    form.find('button[type=submit],input,select').prop('disabled', function (i, v) {
      return !v;
    })
  }

  $('#query-form').submit(function  (e) {
    var $this = $(this), payload = $this.serialize(), $table = $('#query-table');
    inputToggle($this);
    $table.hide();
    $.post($this.attr('action'), payload, function (response) {
      for (item in response.order)
      {
        $table.find('td[rel=' + item + ']').text(response.order[item]);
      }
      $table.show();
      $this.find('input').val("");
      inputToggle($this);
    }).fail(function (xhr) {
      errHandler(xhr, $this);
      if (xhr.status === 404) {
        $this.find('input').val("");
      }
      inputToggle($this);
    });

    return false;
  });

  $('#payment-form').submit(function (e) {
    return true;

    var $this = $(this), payload = $('#payment-form').serialize();

    e.preventDefault();

    inputToggle($this);

    $.post($this.attr('action'), payload, function (response) {
      if (response.success === true) {
        swal({
          title: "Thank you for your payment!",
          text: "Your transaction id is <u>" + response.order.transaction_id + "</u>!",
          type: "success",
          html: true
        }, function (isConfirm) {
          if (isConfirm) {
            window.location.reload(); // refresh csrf
          }
        });
      } else {
        swal({
          title: "Something went wrong...",
          text: "Your payment request cannot be processed.<br />Reason: <strong>" + response.message + "</strong>",
          type: "error",
          html: true
        });
      }
      inputToggle($this);
    }).fail(function (xhr) {
      errHandler(xhr, $this);
      inputToggle($this);
    });

    return false;
  });
});
</script>
</body>
</html>