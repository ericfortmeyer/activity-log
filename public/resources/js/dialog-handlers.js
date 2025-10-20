(function() {
  const dialogs = ['report', 'remarks', 'credit-hours'];
  const dialogIdSuffix = '-dialog';
  const dialogOpenButtonIdSuffix = '-dialog-open-button';
  const dialogCloseButtonIdSuffix = '-dialog-close-button';

  // Ensure the DOM is fully loaded before running the script
  document.addEventListener('DOMContentLoaded', () => {

    dialogs.forEach(dialogName => {
      const dialogId = dialogName.concat(dialogIdSuffix);
      const dialogOpenButtonId = dialogName.concat(dialogOpenButtonIdSuffix);
      const dialogCloseButtonId = dialogName.concat(dialogCloseButtonIdSuffix);

      dialoagHandler({
        dialogId,
        dialogOpenButtonId,
        dialogCloseButtonId,
      });
    });

  }); // End of DOMContentLoaded event listener
})(); // End of IIFE

function dialoagHandler( {dialogId, dialogOpenButtonId, dialogCloseButtonId }) {
    // Elements related to the report dialog functionality
    const dialog = document.getElementById(dialogId);
    const dialogOpenButton = document.getElementById(dialogOpenButtonId);
    const dialogCloseButton = document.getElementById(dialogCloseButtonId);

    if (!dialog) {
      return;
    }

    // Open button functionality
    if (dialogOpenButton) {
      dialogOpenButton.addEventListener('click', () => {
        dialog.showModal();
      });
    }

    // Close button functionality
    if (dialogCloseButton) {
      dialogCloseButton.addEventListener('click', () => {
        dialog.close();
      });
    }
}