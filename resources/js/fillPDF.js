import {
    error,
    PDFDocument,
    rgb,
    StandardFonts,
    TextAlignment,
    PDFFont,
} from "pdf-lib";


async function fillPDFForm(arrayBuffer, fieldValues) {
    try {
        const pdfDoc = await PDFDocument.load(arrayBuffer);
        const form = pdfDoc.getForm();

        // Embed font
        const myFont = await pdfDoc.embedFont(StandardFonts.Helvetica);
        for (const [fieldName, value] of Object.entries(fieldValues)) {
            try {
                const field = form.getField(fieldName);
                if (field.constructor.name === "PDFTextField2") {
                    const sanitizedValue = value ? String(value) : ""; // Ensure value is valid
                    field.setText(sanitizedValue);
                    field.setAlignment(TextAlignment.Center);
                    field.setFontSize(13);
                } else if (field.constructor.name === "PDFCheckBox2") {
                    value ? field.check() : field.uncheck();
                } else {
                    console.warn(`Unsupported field type for ${fieldName}`);
                }
            } catch (error) {
                console.error(`Error processing field "${fieldName}":`, error);
            }
        }
        form.flatten();
        const filledPdfBytes = await pdfDoc.save();
        previewPDF(filledPdfBytes);
    } catch (error) {
        console.error("Error filling PDF form:", error);
    }
}

async function previewPDF(pdfBytes) {
    // Create a FormData object
    const formData = new FormData();
    const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });
    formData.append('file', pdfBlob, 'filled-form.pdf');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const uploadUrl = `/contracts/${contract.id}/uploadPDF`;
    // Send the FormData to the server
    await fetch(uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData,
    });
    const blob = new Blob([pdfBytes], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);
    window.open(url, "_blank");
}

function downloadPDF(pdfBytes, fileName) {
    const blob = new Blob([pdfBytes], { type: "application/pdf" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = fileName;
    link.click();
}

document.addEventListener("DOMContentLoaded", async () => {
    //    const pdfFileUrl = "/storage/forms/HR-LB-25.pdf"; // Make sure this is the correct URL

    const pdfFileUrl = contractPDF; // Make sure this is the correct URL

    try {
        const response = await fetch(pdfFileUrl);

        // Check if the response is OK (200-299)
        if (!response.ok) {
            throw new Error("File not found or other error");
        }

        // Get the PDF data as a Blob
        const blob = await response.blob(); // Fetch the blob (binary data)

        // Convert the blob into an ArrayBuffer
        const arrayBuffer = await blob.arrayBuffer();

        // Prepare the fieldValues (this should be passed to the view from Laravel)
        //const fieldValues = fieldValues || {}; // Assuming `fieldValues` is defined in the Blade template
        //console.log(window.fieldValues);
        // Call the function to fill the PDF form
        fillPDFForm(arrayBuffer, fieldValues,);
    } catch (error) {
        console.error("Error fetching PDF:", error);
    }
});
