// resources/js/extractFields.js
import { PDFDocument } from 'pdf-lib';

// async function extractFieldsFromPDF(file) {
//     let fieldData = null;
//     const reader = new FileReader();
//     reader.readAsArrayBuffer(file);

//     reader.onload = async (e) => {
//         const arrayBuffer = e.target.result;
//         const pdfDoc = await PDFDocument.load(arrayBuffer);

//         const form = pdfDoc.getForm();
//         const fields = form.getFields();

//         fieldData = fields.map((field) => ({
//             name: field.getName(),
//             type: field.constructor.name, // Example: 'TextField', 'DropdownField', etc.
//             required: field.isRequired,
//             readOnly: field.isReadOnly,
//         }));


//        // displayFields(fieldData);
//     };
//     return fieldData;

// }
async function extractFieldsFromPDF(file) {
    const reader = new FileReader();

    // Return a Promise so that you can await the result
    return new Promise((resolve, reject) => {
        reader.readAsArrayBuffer(file);

        reader.onload = async (e) => {
            try {
                const arrayBuffer = e.target.result;
                const pdfDoc = await PDFDocument.load(arrayBuffer);

                const form = pdfDoc.getForm();
                const fields = form.getFields();
                console.log(fields);

                const fieldData = fields.map((field) => ({
                    name: field.getName(),
                    type: field.constructor.name, // Example: 'TextField', 'DropdownField', etc.
                }));

                resolve(fieldData); // Resolve the promise with fieldData
            } catch (error) {
                reject(error); // Reject the promise if there's an error
            }
        };

        reader.onerror = (error) => reject(error); // Handle file read errors
    });
}

window.extractFieldsFromPDF = extractFieldsFromPDF;
function displayFields(fields) {
    const outputDiv = document.querySelector('#fields-output');
    outputDiv.innerHTML = ''; // Clear any existing data

    if (fields.length === 0) {
        outputDiv.innerHTML = '<p>No form fields found in the PDF.</p>';
        return;
    }

    const list = document.createElement('ul');

    fields.forEach((field) => {
        const listItem = document.createElement('li');
        listItem.textContent = `Name: ${field.name}, Type: ${field.type}`;
        list.appendChild(listItem);
    });

    outputDiv.appendChild(list);
}


