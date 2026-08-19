<script>
    var LETTER_PRINT_STYLES = {!! json_encode(view('letter::letter.pdf.letter_pdf_css')->render()) !!};

    function printLetterPreview() {
        let printFrame = document.createElement('iframe');
        let html = '<html><head><title>Print</title>';
        html += LETTER_PRINT_STYLES;
        html += '</head><body class="letter-content">';
        html += $('#descriptionPreviewArea').html();
        html += '</body></html>';
        printFrame.style.display = 'none';
        document.body.appendChild(printFrame);

        printFrame.contentDocument.open();
        printFrame.contentDocument.write(html);
        printFrame.contentDocument.close();

        printFrame.onload = function() {
            printFrame.contentWindow.print();
            printFrame.contentWindow.onafterprint = function() {
                document.body.removeChild(printFrame);
            };
        };
    }

    $(document).ready(function() {
        $('#printButton').on('click', printLetterPreview);
    });
</script>
