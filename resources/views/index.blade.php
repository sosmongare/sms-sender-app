<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMS Sender</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>

  <div class="container my-4">
    <h1 class="text-center mb-4">SMS Sender</h1>
    
    <div class="text-center">
    <p><strong>SMS Balance: </strong> {{ $balance }} Units</p>
      <p>
        Top up your Account via M-PESA: 
        Paybill: 969610
        Account: SOSMO
      </p>
    </div>
    <hr>
    <form method="post" action="{{ route('send-sms') }}">
        @csrf

        @if(session('success'))
          <div class="alert alert-success mt-3">
            {{ session('success') }}
          </div>
        @elseif(session('error'))
          <div class="alert alert-danger mt-3">
            {{ session('error') }}
          </div>
        @endif
        <div class="form-group">
          <label for="mobilenumber">Mobile Number:</label>
          <input type="tel" class="form-control" id="mobilenumber" name="mobilenumber" placeholder="Enter mobile number">
        </div>
        <div class="form-group">
          <label for="message">Message:</label>
          <textarea class="form-control" id="message" name="message" rows="5"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>

        
    </form>

  </div>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
  <script>
     $(document).ready(function() {
        $(".alert").fadeTo(5000, 500).slideUp(500, function(){
         $(".alert").alert('close');
       });
     });
  </script>
</body>
</html>
