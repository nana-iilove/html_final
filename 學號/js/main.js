const navToggle = document.querySelector(".nav-toggle");
const navLinks = document.querySelector("#nav-links");
const navItems = Array.from(document.querySelectorAll(".nav-links a"));
const sections = navItems
  .map((item) => document.querySelector(item.getAttribute("href")))
  .filter(Boolean);

document.querySelector("#year").textContent = new Date().getFullYear();

navToggle.addEventListener("click", () => {
  const isOpen = navLinks.classList.toggle("open");
  navToggle.setAttribute("aria-expanded", String(isOpen));
});

navItems.forEach((item) => {
  item.addEventListener("click", () => {
    navLinks.classList.remove("open");
    navToggle.setAttribute("aria-expanded", "false");
  });
});

const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;

      navItems.forEach((item) => {
        item.classList.toggle(
          "active",
          item.getAttribute("href") === `#${entry.target.id}`
        );
      });
    });
  },
  { rootMargin: "-45% 0px -45% 0px", threshold: 0.01 }
);

sections.forEach((section) => observer.observe(section));
