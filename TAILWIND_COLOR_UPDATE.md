# CẬP NHẬT MỚI: SỬ DỤNG CÚ PHÁP TAILWIND CSS VỚI VAR()

## Thay đổi cách implement màu sắc:

**Trước đây:** Sử dụng SCSS variables và CSS properties

```scss
.btn-primary {
    background-color: $color-primary;

    &:hover {
        background-color: $color-primary-hover;
    }
}
```

**Bây giờ:** Sử dụng cú pháp Tailwind CSS với `var()`

```scss
.btn-primary {
    @apply bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)];
}
```

## Các pattern cú pháp mới:

### Background Colors:

- `@apply bg-[var(--color-primary)]`
- `@apply bg-[var(--color-secondary)]`
- `@apply bg-[var(--color-bg-card)]`

### Text Colors:

- `@apply text-[var(--color-text-primary)]`
- `@apply text-[var(--color-text-secondary)]`
- `@apply text-[var(--color-text-danger)]`

### Border Colors:

- `@apply border-[var(--color-border-light)]`
- `@apply border-[var(--color-primary-dark)]`

### Focus Ring Colors:

- `@apply focus:ring-[var(--color-focus-blue)]`
- `@apply focus:ring-[var(--color-focus-indigo)]`

## Các file đã được cập nhật với cú pháp mới:

### 1. `common.scss`

```scss
// Form section
.form-section {
    @apply mb-6 rounded-md bg-[var(--color-bg-card)] p-6 shadow-md;
}

// Labels
.form-group label {
    @apply mb-2 text-sm font-semibold text-[var(--color-text-secondary)];
}

// Input fields
input,
textarea,
select {
    @apply w-full rounded-md border-[var(--color-border-light)] px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-[var(--color-focus-blue)];
}

// Primary button
.btn-primary {
    @apply bg-[var(--color-primary)] text-white hover:bg-[var(--color-primary-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-opacity-50;
}

// Secondary button
.btn-secondary {
    @apply bg-[var(--color-secondary-light)] text-gray-800 hover:bg-[var(--color-secondary-medium)];
}

// Danger button
.btn-remove {
    @apply mx-auto my-4 flex justify-center bg-[var(--color-danger)] text-white;
}

// Table button
.btn-table {
    @apply rounded bg-[var(--color-primary)] px-2 py-1 text-xs text-white hover:bg-[var(--color-primary-hover)];
}

// Data table
.data-table thead {
    @apply bg-[var(--color-primary-hover)] text-white;

    th {
        @apply whitespace-nowrap border-[var(--color-primary-dark)] px-4 py-2 text-center;
    }
}

.data-table tbody tr:hover {
    @apply bg-[var(--color-bg-main)];
}

.data-table tbody td {
    @apply whitespace-nowrap border-[var(--color-border-light)] px-4 py-2 text-center;
}
```

### 2. `components/_error.scss`

```scss
.not-found {
    @apply flex h-screen flex-col items-center justify-center bg-[var(--color-secondary)];

    &-code {
        @apply mb-4 text-9xl font-bold text-[var(--color-primary-hover)];
    }

    &-message {
        @apply mb-8 text-2xl text-[var(--color-text-secondary)];
    }

    &-button {
        @apply rounded bg-[var(--color-primary)] px-4 py-2 font-bold text-white hover:bg-[var(--color-primary-dark)] focus:outline-none;
    }
}
```

### 3. `components/_loading.scss`

```scss
.loading-overlay {
    @apply absolute inset-0 flex h-full w-full items-center justify-center bg-[var(--color-bg-card)];

    .spinner {
        @apply inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-[var(--color-primary)] border-t-transparent align-middle;
    }
}
```

### 4. `components/_navbar.scss`

```scss
.navbar {
    @apply mx-auto my-[20px] max-w-[1140px] overflow-hidden rounded-md border-[var(--color-border-light)] shadow-md;

    &-menu {
        @apply grid w-full grid-cols-5 items-center justify-between divide-x-[1px] divide-[var(--color-border-light)] bg-[var(--color-secondary)];
    }
}

.menu-item {
    @apply flex h-full w-full cursor-pointer items-center justify-center px-5 py-4 text-center text-[16px] font-semibold uppercase text-[var(--color-text-secondary)] transition-colors duration-200 ease-in-out hover:bg-[var(--color-secondary-light)];
}

.active {
    @apply bg-[var(--color-primary)] text-white hover:bg-[var(--color-primary-hover)];
}
```

### 5. `components/_student-modal.scss`

```scss
.modal {
    &__overlay {
        @apply fixed inset-0 bg-[var(--color-bg-overlay)] transition-opacity;
    }

    &__container {
        @apply relative inline-block transform overflow-hidden rounded-lg bg-[var(--color-bg-card)] text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle;
    }

    &__content {
        @apply bg-[var(--color-bg-card)] px-4 pb-4 pt-5 sm:p-6 sm:pb-4;
    }

    &__title {
        @apply text-center text-[30px] font-medium leading-6 text-[var(--color-text-primary)];
    }

    &__footer {
        @apply bg-[var(--color-bg-main)] px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6;

        &--action-primary {
            @apply inline-flex w-full justify-center rounded-md border border-transparent bg-[var(--color-primary)] px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-[var(--color-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--color-focus-blue)] focus:ring-offset-2 sm:ml-3 sm:w-auto;
        }

        &--action-secondary {
            @apply mt-3 inline-flex w-full justify-center rounded-md border-[var(--color-border-light)] bg-[var(--color-bg-card)] px-4 py-2 text-base font-medium text-[var(--color-text-secondary)] shadow-sm hover:bg-[var(--color-bg-main)] focus:outline-none focus:ring-2 focus:ring-[var(--color-focus-indigo)] focus:ring-offset-2 sm:ml-3 sm:mt-0 sm:w-auto;
        }
    }
}
```

### 6. `pages/__login.scss`

```scss
.auth-login {
    @apply mx-auto my-[50px] max-w-[600px] overflow-hidden rounded-md border-[var(--color-border-light)] shadow-md;

    &-body {
        @apply flex flex-col items-stretch justify-center bg-[var(--color-secondary)] p-6;
    }

    .form-group input {
        @apply w-full rounded-md border-[var(--color-border-light)] p-3 outline-none;
    }

    .form-submit button {
        @apply w-full rounded-md bg-[var(--color-primary)] px-6 py-3 font-bold text-white hover:bg-[var(--color-primary-dark)];
    }
}
```

### 7. `pages/__diploma-management.scss`

```scss
.diploma-management {
    @apply mx-auto max-w-[1140px] bg-[var(--color-secondary)] px-4 py-6;

    .form-group select {
        @apply min-w-[400px] appearance-none bg-[var(--color-bg-card)] pr-8;
    }
}
```

### 8. `pages/__embryo-management.scss`

```scss
.embryo-management {
    @apply mx-auto mt-10 max-w-[1140px] bg-[var(--color-secondary)] px-4 py-6;
}
```

## Lợi ích của cú pháp Tailwind mới:

1. **Nhất quán với Tailwind CSS**: Sử dụng syntax chuẩn của Tailwind
2. **Hover states tích hợp**: `hover:bg-[var(--color-name)]`
3. **Focus states tích hợp**: `focus:ring-[var(--color-name)]`
4. **Responsive classes**: Có thể kết hợp với `sm:`, `md:`, `lg:`
5. **IntelliSense support**: Editor có thể autocomplete các class Tailwind
6. **Dễ debug**: Có thể xem trực tiếp classes trong Developer Tools

## Cách sử dụng patterns phổ biến:

### Button với nhiều states:

```scss
.btn-custom {
    @apply bg-[var(--color-primary)] text-white hover:bg-[var(--color-primary-hover)] focus:ring-2 focus:ring-[var(--color-focus-blue)] disabled:bg-[var(--color-secondary-medium)];
}
```

### Form input với validation:

```scss
.input-field {
    @apply w-full rounded-md border-[var(--color-border-light)] px-4 py-2 outline-none focus:border-transparent focus:ring-2 focus:ring-[var(--color-focus-blue)];

    &.error {
        @apply border-[var(--color-danger)] focus:ring-[var(--color-danger)];
    }

    &.success {
        @apply border-[var(--color-success)] focus:ring-[var(--color-success)];
    }
}
```

### Card component:

```scss
.card {
    @apply rounded-md border-[var(--color-border-light)] bg-[var(--color-bg-card)] p-6 shadow-md;

    &-header {
        @apply mb-4 text-lg font-semibold text-[var(--color-text-primary)];
    }

    &-body {
        @apply text-[var(--color-text-secondary)];
    }
}
```

### Table với responsive:

```scss
.data-table {
    @apply w-full border-collapse text-sm;

    thead {
        @apply bg-[var(--color-primary-hover)] text-white;

        th {
            @apply whitespace-nowrap border-[var(--color-primary-dark)] px-4 py-2 text-center;
        }
    }

    tbody {
        tr {
            @apply hover:bg-[var(--color-bg-main)] sm:hover:bg-[var(--color-secondary-light)];
        }

        td {
            @apply whitespace-nowrap border-[var(--color-border-light)] px-4 py-2 text-center;
        }
    }
}
```

## Kết luận:

Dự án đã được cập nhật hoàn toàn để sử dụng cú pháp Tailwind CSS hiện đại với `var()`. Tất cả 9 files SCSS đã được refactor để:

✅ **Sử dụng `@apply` với `var(--color-name)`**  
✅ **Tích hợp hover/focus states trực tiếp**  
✅ **Syntax nhất quán và dễ đọc**  
✅ **Tận dụng sức mạnh của Tailwind CSS**  
✅ **Dễ maintain và scale**

Điều này giúp code trở nên hiện đại hơn, dễ maintain hơn và tương thích tốt với hệ sinh thái Tailwind CSS!
