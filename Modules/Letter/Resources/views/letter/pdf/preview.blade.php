<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang($pageTitle)</title>
    @include('letter::letter.pdf.letter_pdf_css')
</head>
<body class="letter-content">
    {!! $letter !!}
</body>
</html>
