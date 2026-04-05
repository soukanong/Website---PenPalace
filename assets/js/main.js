function toggleSearchPanel() {
  const searchPanel = document.getElementById("search-panel");
  searchPanel.style.display =
    searchPanel.style.display === "none" ? "block" : "none";
}

function closeSearchPanel() {
  const searchPanel = document.getElementById("search-panel");
  searchPanel.style.display = "none";
}
