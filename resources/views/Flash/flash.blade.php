<!--- FLASH MESSAGES --->
@if (Session::has('success'))
<script>
    Swal.fire({
        // position: 'top-end',
        icon: 'success',
        html: '{{ Session::get("success") }}',
        showConfirmButton: false,
        timer: 1500
    })
</script>

@elseif (Session::has('error'))

<script>
    Swal.fire({
        // position: 'top-end',
        icon: 'error',
        html: '{{ Session::get("error") }}',
        showConfirmButton: false,
        timer: 1500
    })
</script>

@elseif (Session::has('warning'))

<script>
    Swal.fire({
        // position: 'top-end',
        icon: 'warning',
        html: '{{ Session::get("warning") }}',
        showConfirmButton: false,
        timer: 1500
    })
</script>

@elseif (Session::has('info'))

<script>
    Swal.fire({
        // position: 'top-end',
        icon: 'info',
        html: "{{ Session::get('info') }}",
        showConfirmButton: false,
        timer: 1500
    })
</script>

@elseif ($errors->any())

<script>
    Swal.fire({
        // position: 'top-end',
        icon: 'error',
        html: 'Please check the form below for errors',
        showConfirmButton: false,
        timer: 1500
    })
</script>
@endif
<!--- // END FLASH MESSAGES // --->
