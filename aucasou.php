/* Pages detail anulation*/
a.dropdown-toggle::after {
    display: none !important;
    content: none !important;
}
.dropdown-menu .dropdown-item:hover {
  background-color: transparent !important;
  box-shadow: none !important;
}
.dropdown-menu {
  padding: 0.25rem 0.5rem !important; 
  font-size: 0.85rem; 
  min-width: 100px !important; 
}
.dropdown-menu .dropdown-item {
  padding: 0.1rem 0.2rem !important; 
}
/* Style identique à ul.nav .nav-link:hover pour le dropdown */
.dropdown-menu .dropdown-item:not(.text-danger):hover {
  color: var(--eco-green-dark) !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
  background-color: transparent !important;
}