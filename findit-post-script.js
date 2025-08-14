document.addEventListener("DOMContentLoaded", () => {
  if (!document.getElementById("findit-search-form")) return;
  const form = document.getElementById("findit-search-form");
  const resultsContainer = document.getElementById(
    "findit-post-search-results"
  );
  const loadMoreBtn = document.getElementById("findit-load-more");
  let currentPage = 1;

  function toggleLoadMore(visible) {
    loadMoreBtn.style.display = visible ? "block" : "none";
  }

  function getFormData() {
    return {
      search_text: document.getElementById("findit-search-text").value,
      selected_category: document.getElementById("findit-filter-category")
        .value,
      selected_tag: document.getElementById("findit-filter-tag").value,
      findit_search_nonce: findit_ajax_obj.nonce,
      page: currentPage,
      action: "post_search",
    };
  }

  function performSearch(loadMore = false) {
    if (!loadMore) {
      currentPage = 1;
      resultsContainer.style.opacity = 0;
    }

    const data = new URLSearchParams(getFormData());

    fetch(findit_ajax_obj.ajaxurl, {
      method: "POST",
      body: data,
    })
      .then((res) => res.json())
      .then((json) => {
        if (json.success) {
          if (!loadMore) {
            setTimeout(() => {
              resultsContainer.innerHTML = json.data.html;
              toggleLoadMore(json.data.has_more);

              resultsContainer.style.opacity = 1;
              highlightKeyword();
            }, 200);
          } else {
            resultsContainer.insertAdjacentHTML("beforeend", json.data.html);
            toggleLoadMore(json.data.has_more);
            highlightKeyword();
          }
        } else {
          console.error(json.data.message || "Search failed");
          toggleLoadMore(false);
        }
      })
      .catch((err) => {
        console.error("AJAX error:", err);
        toggleLoadMore(false);
      });
  }
  function highlightKeyword() {
    const keyword = document.getElementById("findit-search-text").value.trim();
    if (!keyword) return;

    const cards = document.querySelectorAll(".findit-card");
    const regex = new RegExp(
      `(${keyword.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&")})`,
      "gi"
    );

    cards.forEach((card) => {
      ["title", "excerpt"].forEach((field) => {
        const el = card.querySelector(`.ss-${field}`);
        if (el) {
          const originalHTML = el.innerHTML;

          const cleanedHTML = originalHTML.replace(/<\/?mark>/g, "");

          el.innerHTML = cleanedHTML.replace(regex, "<mark>$1</mark>");
        }
      });
    });
  }

  form?.addEventListener("submit", (e) => {
    e.preventDefault();
    performSearch(false);
  });

  document.getElementById("reset-search")?.addEventListener("click", () => {
    form.reset();
    performSearch(false);
  });

  loadMoreBtn?.addEventListener("click", () => {
    currentPage++;
    performSearch(true);
  });

  performSearch(false);
});
