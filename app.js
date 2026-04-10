const FARM_KEY = "souqFarm";
const PRODUCTS_KEY = "souqProducts";
const EDIT_INDEX_KEY = "souqEditProductIndex";
const PRODUCT_DATE_TYPES = ["Ajwa", "Khalas", "Sukkari"];

function getFarm() {
  return JSON.parse(localStorage.getItem(FARM_KEY)) || null;
}

function saveFarm(farm) {
  localStorage.setItem(FARM_KEY, JSON.stringify(farm));
}

function clearFarm() {
  localStorage.removeItem(FARM_KEY);
}

function getProducts() {
  return JSON.parse(localStorage.getItem(PRODUCTS_KEY)) || [];
}

function saveProducts(products) {
  localStorage.setItem(PRODUCTS_KEY, JSON.stringify(products));
}

function clearProducts() {
  localStorage.removeItem(PRODUCTS_KEY);
}

function setEditProductIndex(index) {
  localStorage.setItem(EDIT_INDEX_KEY, String(index));
}

function getEditProductIndex() {
  const index = localStorage.getItem(EDIT_INDEX_KEY);
  return index === null ? null : Number(index);
}

function clearEditProductIndex() {
  localStorage.removeItem(EDIT_INDEX_KEY);
}

function buildDateTypeOptions(selectedValue = "") {
  return [
    '<option value="">Select date type</option>',
    ...PRODUCT_DATE_TYPES.map((type) => {
      const selected = type === selectedValue ? ' selected' : "";
      return `<option value="${escapeHtml(type)}"${selected}>${escapeHtml(type)}</option>`;
    })
  ].join("");
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function showFormMessage(element, message, type) {
  if (!element) return;
  element.textContent = message;
  element.className = "form-message";

  if (message && type) {
    element.classList.add(type);
  }
}

function redirectAfterDelay(path, delay = 700) {
  window.setTimeout(() => {
    window.location.href = path;
  }, delay);
}

function showMessage(errorElement, successElement, errorMsg = "", successMsg = "") {
  if (errorElement) errorElement.textContent = errorMsg;
  if (successElement) successElement.textContent = successMsg;
}

function initCreateFarmPage() {
  const form = document.getElementById("createFarmForm");
  if (!form) return;

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const farmName = document.getElementById("farmName").value.trim();
    const region = document.getElementById("region").value;
    const price = document.getElementById("farmPrice").value.trim();
    const quantity = document.getElementById("farmQuantity").value.trim();
    const description = document.getElementById("farmDescription").value.trim();
    const checkedTypes = Array.from(document.querySelectorAll('input[name="dateType"]:checked'));
    const errorBox = document.getElementById("farmError");
    const successBox = document.getElementById("farmSuccess");
    const formMessage = document.getElementById("farmFormMessage");

    showMessage(errorBox, successBox);
    showFormMessage(formMessage, "");

    if (!farmName) {
      if (formMessage) {
        showFormMessage(formMessage, "Please enter the farm name.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Please enter the farm name.");
      }
      return;
    }

    if (!region || region === "Select Region") {
      if (formMessage) {
        showFormMessage(formMessage, "Please select a region.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Please select a region.");
      }
      return;
    }

    if (checkedTypes.length === 0) {
      if (formMessage) {
        showFormMessage(formMessage, "Please select at least one date type.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Please select at least one date type.");
      }
      return;
    }

    if (price === "" || Number.isNaN(Number(price)) || Number(price) < 0) {
      if (formMessage) {
        showFormMessage(formMessage, "Price must be a valid number greater than or equal to 0.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Price must be a valid number greater than or equal to 0.");
      }
      return;
    }

    if (quantity === "" || Number.isNaN(Number(quantity)) || Number(quantity) < 0) {
      if (formMessage) {
        showFormMessage(formMessage, "Quantity must be a valid number greater than or equal to 0.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Quantity must be a valid number greater than or equal to 0.");
      }
      return;
    }

    if (!description) {
      if (formMessage) {
        showFormMessage(formMessage, "Please enter the farm description.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Please enter the farm description.");
      }
      return;
    }

    saveFarm({
      farmName,
      region,
      dateTypes: checkedTypes.map((item) => item.value),
      price: Number(price),
      quantity: Number(quantity),
      description,
      verifiedStatus: "Pending",
      trustedBadge: false
    });

    if (formMessage) {
      showFormMessage(formMessage, "Farm profile created successfully.", "success-message");
    } else {
      showMessage(errorBox, successBox, "", "Farm profile created successfully.");
    }
    redirectAfterDelay("Farmerdashboard.html");
  });
}

function initAddProductPage() {
  const form = document.getElementById("addProductForm");
  if (!form) return;

  const formMessage = document.getElementById("addProductMessage");
  const lockedNotice = document.getElementById("addProductLocked");
  const dateTypeSelect = document.getElementById("dateType");
  const productImageInput = document.getElementById("productImage");
  const imagePreview = document.getElementById("imagePreview");
  const farm = getFarm();
  let imageData = "";

  if (!farm) {
    if (lockedNotice) lockedNotice.hidden = false;
    form.hidden = true;
    return;
  }

  if (lockedNotice) lockedNotice.hidden = true;

  if (dateTypeSelect) {
    dateTypeSelect.innerHTML = buildDateTypeOptions();
  }

  if (productImageInput && imagePreview) {
    productImageInput.addEventListener("change", () => {
      const file = productImageInput.files?.[0];

      if (!file) {
        imageData = "";
        imagePreview.src = "";
        imagePreview.style.display = "none";
        return;
      }

      const reader = new FileReader();
      reader.onload = (loadEvent) => {
        imageData = loadEvent.target?.result || "";
        imagePreview.src = imageData;
        imagePreview.style.display = imageData ? "block" : "none";
      };
      reader.readAsDataURL(file);
    });
  }

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const productName = document.getElementById("productName").value.trim();
    const dateType = document.getElementById("dateType").value;
    const price = document.getElementById("price").value.trim();
    const quantity = document.getElementById("quantity").value.trim();
    const origin = document.getElementById("origin").value;
    const description = document.getElementById("description").value.trim();

    if (!productName || !dateType || !price || !quantity || !origin || !description) {
      showFormMessage(formMessage, "Please fill in all required fields.", "error-message");
      return;
    }

    if (Number.isNaN(Number(price)) || Number(price) < 0) {
      showFormMessage(formMessage, "Please enter a valid price.", "error-message");
      return;
    }

    if (Number.isNaN(Number(quantity)) || Number(quantity) < 0) {
      showFormMessage(formMessage, "Please enter a valid quantity.", "error-message");
      return;
    }

    const products = getProducts();
    products.push({
      productName,
      dateType,
      price: Number(price),
      quantity: Number(quantity),
      region: origin,
      origin,
      description,
      farmName: farm.farmName,
      imageUrl: imageData || ""
    });

    saveProducts(products);
    showFormMessage(formMessage, "Product added successfully.", "success-message");
    form.reset();
    imageData = "";
    if (imagePreview) {
      imagePreview.src = "";
      imagePreview.style.display = "none";
    }
    redirectAfterDelay("Farmerdashboard.html");
  });
}

function initEditProductPage() {
  const form = document.getElementById("editProductForm");
  if (!form) return;

  const products = getProducts();
  const editIndex = getEditProductIndex();
  const errorBox = document.getElementById("productError");
  const successBox = document.getElementById("productSuccess");
  const formMessage = document.getElementById("productFormMessage");
  const imageInput = document.getElementById("productImage");
  const imagePreview = document.getElementById("imagePreview");

  if (editIndex === null || !products[editIndex]) {
    window.alert("Please select a product from the dashboard first.");
    window.location.href = "Farmerdashboard.html";
    return;
  }

  const product = products[editIndex];
  let imageData = product.imageUrl || "";
  const dateTypeSelect = document.getElementById("dateType");
  if (dateTypeSelect) {
    dateTypeSelect.innerHTML = buildDateTypeOptions(product.dateType);
  }
  document.getElementById("productName").value = product.productName;
  document.getElementById("price").value = product.price;
  document.getElementById("quantity").value = product.quantity;
  document.getElementById("originRegion").value = product.origin || product.region || "";
  document.getElementById("productDescription").value = product.description;

  if (imagePreview && imageData) {
    imagePreview.src = imageData;
    imagePreview.style.display = "block";
  }

  if (imageInput && imagePreview) {
    imageInput.addEventListener("change", () => {
      const file = imageInput.files?.[0];

      if (!file) {
        imageData = product.imageUrl || "";
        imagePreview.src = imageData;
        imagePreview.style.display = imageData ? "block" : "none";
        return;
      }

      const reader = new FileReader();
      reader.onload = (loadEvent) => {
        imageData = loadEvent.target?.result || "";
        imagePreview.src = imageData;
        imagePreview.style.display = imageData ? "block" : "none";
      };
      reader.readAsDataURL(file);
    });
  }

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const productName = document.getElementById("productName").value.trim();
    const dateType = document.getElementById("dateType").value;
    const price = document.getElementById("price").value.trim();
    const quantity = document.getElementById("quantity").value.trim();
    const region = document.getElementById("originRegion").value;
    const description = document.getElementById("productDescription").value.trim();
    showMessage(errorBox, successBox);
    showFormMessage(formMessage, "");

    if (!productName || !dateType || !price || !quantity || !region || !description) {
      if (formMessage) {
        showFormMessage(formMessage, "Please fill in all required fields.", "error-message");
      } else if (!productName) {
        showMessage(errorBox, successBox, "Please enter the product name.");
      } else if (dateType === "" || dateType === "Select Date Type") {
        showMessage(errorBox, successBox, "Please select a date type.");
      } else if (price === "" || Number.isNaN(Number(price)) || Number(price) < 0) {
        showMessage(errorBox, successBox, "Price must be a valid number greater than or equal to 0.");
      } else if (quantity === "" || Number.isNaN(Number(quantity)) || Number(quantity) < 0) {
        showMessage(errorBox, successBox, "Quantity must be a valid number greater than or equal to 0.");
      } else if (region === "" || region === "Select Region") {
        showMessage(errorBox, successBox, "Please select the region.");
      } else {
        showMessage(errorBox, successBox, "Please enter the product description.");
      }
      return;
    }

    if (Number.isNaN(Number(price)) || Number(price) < 0) {
      if (formMessage) {
        showFormMessage(formMessage, "Please enter a valid price.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Price must be a valid number greater than or equal to 0.");
      }
      return;
    }

    if (Number.isNaN(Number(quantity)) || Number(quantity) < 0) {
      if (formMessage) {
        showFormMessage(formMessage, "Please enter a valid quantity.", "error-message");
      } else {
        showMessage(errorBox, successBox, "Quantity must be a valid number greater than or equal to 0.");
      }
      return;
    }

    products[editIndex] = {
      ...products[editIndex],
      productName,
      dateType,
      price: Number(price),
      quantity: Number(quantity),
      region,
      origin: region,
      description
    };

    if (imageData) {
      products[editIndex].imageUrl = imageData;
    }

    saveProducts(products);
    clearEditProductIndex();
    if (formMessage) {
      showFormMessage(formMessage, "Product updated successfully.", "success-message");
    } else {
      showMessage(errorBox, successBox, "", "Product updated successfully.");
    }
    redirectAfterDelay("Farmerdashboard.html");
  });
}

function renderProductsTable(productsTableBody, products) {
  if (!productsTableBody) return;

  if (products.length === 0) {
    productsTableBody.innerHTML = `
      <div class="empty-products-state">No products added yet.</div>
    `;
    return;
  }

  productsTableBody.innerHTML = products.map((product, index) => `
    <article class="dashboard-product-row">
      <div class="dashboard-product-col dashboard-product-col--image">
        ${product.imageUrl ? `<img class="dashboard-product-image" src="${product.imageUrl}" alt="${escapeHtml(product.productName)}">` : '<div class="dashboard-product-image placeholder">No photo</div>'}
      </div>
      <div class="dashboard-product-col dashboard-product-col--name">${escapeHtml(product.productName)}</div>
      <div class="dashboard-product-col">${escapeHtml(product.dateType)}</div>
      <div class="dashboard-product-col">${escapeHtml(product.price)} SAR</div>
      <div class="dashboard-product-col">${escapeHtml(product.quantity)}</div>
      <div class="dashboard-product-col">${escapeHtml(product.origin || product.region || "-")}</div>
      <div class="dashboard-product-col dashboard-product-col--description">
        <span class="description-text">${escapeHtml(product.description || "-")}</span>
      </div>
      <div class="dashboard-product-col dashboard-product-col--actions">
        <div class="table-actions">
          <a class="mini-btn edit" href="editProduct.html" data-index="${product.storageIndex ?? index}">Edit</a>
          <a class="mini-btn delete" href="#" data-delete-index="${product.storageIndex ?? index}">Delete</a>
        </div>
      </div>
    </article>
  `).join("");

  productsTableBody.querySelectorAll("[data-index]").forEach((button) => {
    button.addEventListener("click", () => {
      setEditProductIndex(Number(button.getAttribute("data-index")));
    });
  });

  productsTableBody.querySelectorAll("[data-delete-index]").forEach((button) => {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      deleteProduct(Number(button.getAttribute("data-delete-index")));
    });
  });
}

function initFarmerDashboardPage() {
  const beforeFarmSection = document.getElementById("beforeFarmSection");
  const afterFarmSection = document.getElementById("afterFarmSection");
  const farmStatsSection = document.getElementById("farmStatsSection");
  const farmProfileSection = document.getElementById("farmProfileSection");
  const productsSection = document.getElementById("productsSection");
  const productsTableBody = document.getElementById("dashboardProductsBody");

  if (!beforeFarmSection && !afterFarmSection && !farmStatsSection && !farmProfileSection && !productsSection) {
    return;
  }

  const farm = getFarm();
  const products = getProducts();

  if (!farm) {
    if (beforeFarmSection) beforeFarmSection.style.display = "block";
    if (afterFarmSection) afterFarmSection.style.display = "none";
    if (farmStatsSection) farmStatsSection.style.display = "none";
    if (farmProfileSection) farmProfileSection.style.display = "none";
    if (productsSection) productsSection.style.display = "none";
    return;
  }

  if (beforeFarmSection) beforeFarmSection.style.display = "none";
  if (afterFarmSection) afterFarmSection.style.display = "block";
  if (farmStatsSection) farmStatsSection.style.display = "grid";
  if (farmProfileSection) farmProfileSection.style.display = "block";
  if (productsSection) productsSection.style.display = "block";

  const farmProducts = products
    .map((product, index) => ({ ...product, storageIndex: index }))
    .filter((product) => {
      if (!product.farmName) return true;
      return product.farmName === farm.farmName;
    });

  const farmerWelcomeName = document.getElementById("farmerWelcomeName");
  const totalProducts = document.getElementById("totalProducts");
  const dashboardFarmRegion = document.getElementById("dashboardFarmRegion");
  const dashboardFarmStatus = document.getElementById("dashboardFarmStatus");
  const dashboardFarmName = document.getElementById("dashboardFarmName");
  const dashboardFarmRegionText = document.getElementById("dashboardFarmRegionText");
  const dashboardFarmDateTypes = document.getElementById("dashboardFarmDateTypes");
  const dashboardFarmDescription = document.getElementById("dashboardFarmDescription");
  const productsSubtext = document.getElementById("productsSubtext");
  const resetFarmBtn = document.getElementById("resetFarmBtn");

  if (farmerWelcomeName) farmerWelcomeName.textContent = farm.farmName;
  if (totalProducts) totalProducts.textContent = String(farmProducts.length);
  if (dashboardFarmRegion) dashboardFarmRegion.textContent = farm.region;
  if (dashboardFarmStatus) dashboardFarmStatus.textContent = farm.verifiedStatus || "Pending";
  if (dashboardFarmName) dashboardFarmName.textContent = farm.farmName;
  if (dashboardFarmRegionText) dashboardFarmRegionText.textContent = farm.region;
  if (dashboardFarmDateTypes) dashboardFarmDateTypes.textContent = farm.dateTypes.join(", ");
  if (dashboardFarmDescription) dashboardFarmDescription.textContent = farm.description;
  if (productsSubtext) productsSubtext.textContent = `Current products listed under ${farm.farmName}.`;

  if (resetFarmBtn) {
    resetFarmBtn.addEventListener("click", () => {
      const confirmed = window.confirm("Reset farm profile and remove all products?");
      if (!confirmed) return;

      clearFarm();
      clearProducts();
      clearEditProductIndex();
      window.location.reload();
    });
  }

  renderProductsTable(productsTableBody, farmProducts);
}

function deleteProduct(index) {
  const products = getProducts();
  const confirmed = window.confirm("Are you sure you want to delete this product?");
  if (!confirmed) return;

  products.splice(index, 1);
  saveProducts(products);
  window.location.reload();
}

function goBackSafe(fallbackPath = "Farmerdashboard.html") {
  if (window.history.length > 1) {
    window.history.back();
    return;
  }

  window.location.href = fallbackPath;
}

function setupBackButtons() {
  document.querySelectorAll("[data-back-fallback]").forEach((button) => {
    button.addEventListener("click", () => {
      goBackSafe(button.getAttribute("data-back-fallback"));
    });
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initCreateFarmPage();
  initAddProductPage();
  initEditProductPage();
  initFarmerDashboardPage();
  setupBackButtons();
});
