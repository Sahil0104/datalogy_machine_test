@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="card topbar">
        <div class="brand">
            <h1>Dashboard</h1>
            <p>Protected page with total users and an AJAX DataTable for user management.</p>
        </div>

        <div class="topbar-actions">
            <span class="btn btn-light">Logged in as {{ auth()->user()->name }}</span>
            <button type="button" class="btn btn-primary" id="openAddUserModal">Add User</button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-light">Logout</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="flash success">{{ session('success') }}</div>
    @endif

    <div class="stats-grid">
        <div class="card stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value" id="totalUserCount">{{ $totalUsers }}</div>
            <div class="stat-note">This count updates after add or delete actions so the dashboard always reflects the current database records.</div>
        </div>

        <div class="card stat-card">
            <div class="stat-label">Access Rule</div>
            <div class="stat-value">Auth</div>
            <div class="stat-note">If a logged-in user manually opens login or registration routes, Laravel redirects them back to this dashboard.</div>
        </div>

        <div class="card stat-card">
            <div class="stat-label">Validation</div>
            <div class="stat-value">Dual</div>
            <div class="stat-note">Both jQuery and Laravel validation are active for forms, including duplicate email protection.</div>
        </div>
    </div>

    <div class="card panel">
        <div class="panel-head">
            <div>
                <h2>User List</h2>
                <p>Manage all users here with AJAX loading, editing, and deletion without refreshing the page.</p>
            </div>
        </div>

        <div id="tableMessage" class="ajax-message"></div>

        <table id="usersTable" class="display" style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="modal" id="userModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <div>
                    <h3 id="modalTitle">Add User</h3>
                    <p id="modalSubtitle" style="margin-top:8px; color: var(--muted);">Fill in the details below.</p>
                </div>
                <button type="button" class="close-modal" id="closeUserModal">x</button>
            </div>

            <div class="modal-body">
                <div id="formMessage" class="ajax-message"></div>

                <form id="userForm" novalidate>
                    <input type="hidden" name="user_id" id="user_id">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="modal_first_name">First Name</label>
                            <input type="text" name="first_name" id="modal_first_name" placeholder="First name">
                        </div>

                        <div class="form-group">
                            <label for="modal_last_name">Last Name</label>
                            <input type="text" name="last_name" id="modal_last_name" placeholder="Last name">
                        </div>

                        <div class="form-group full">
                            <label for="modal_email">Email Address</label>
                            <input type="email" name="email" id="modal_email" placeholder="Email address">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" id="cancelUserModal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">Save User</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const modal = $('#userModal');
            const form = $('#userForm');
            const formMessage = $('#formMessage');
            const tableMessage = $('#tableMessage');
            const totalUserCount = $('#totalUserCount');
            const userActionUrlTemplate = @json(url('/users/__ID__'));

            const usersTable = $('#usersTable').DataTable({
                ajax: '{{ route('users.list') }}',
                columns: [
                    { data: 'id' },
                    { data: 'first_name' },
                    { data: 'last_name' },
                    { data: 'email' },
                    { data: 'created_at' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                                <div class="actions">
                                    <button type="button" class="btn btn-light btn-sm edit-user"
                                        data-id="${row.id}"
                                        data-first_name="${row.first_name}"
                                        data-last_name="${row.last_name}"
                                        data-email="${row.email}">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm delete-user" data-id="${row.id}">
                                        Delete
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            function showMessage(target, type, message) {
                target.removeClass('error success').addClass(type).text(message).show();
            }

            function hideMessage(target) {
                target.removeClass('error success').hide().text('');
            }

            function clearFieldErrors() {
                form.find('label.error').remove();
                form.find('input').removeClass('input-error');
            }

            function showFieldError(fieldName, message) {
                const field = form.find(`[name="${fieldName}"]`);
                field.after(`<label class="error">${message}</label>`);
            }

            function validateUserForm() {
                hideMessage(formMessage);
                clearFieldErrors();

                const firstName = $('#modal_first_name').val().trim();
                const lastName = $('#modal_last_name').val().trim();
                const email = $('#modal_email').val().trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                let isValid = true;

                if (!firstName) {
                    showFieldError('first_name', 'First name is required.');
                    isValid = false;
                }

                if (!lastName) {
                    showFieldError('last_name', 'Last name is required.');
                    isValid = false;
                }

                if (!email) {
                    showFieldError('email', 'Email is required.');
                    isValid = false;
                } else if (!emailPattern.test(email)) {
                    showFieldError('email', 'Enter a valid email address.');
                    isValid = false;
                }

                return isValid;
            }

            function refreshStats() {
                $.get('{{ route('users.list') }}', function (response) {
                    totalUserCount.text(response.data.length);
                });
            }

            function openModal(mode, user = null) {
                hideMessage(formMessage);
                form[0].reset();
                clearFieldErrors();

                if (mode === 'edit' && user) {
                    $('#modalTitle').text('Edit User');
                    $('#modalSubtitle').text('Update the user details below.');
                    $('#user_id').val(user.id);
                    $('#modal_first_name').val(user.first_name);
                    $('#modal_last_name').val(user.last_name);
                    $('#modal_email').val(user.email);
                } else {
                    $('#modalTitle').text('Add User');
                    $('#modalSubtitle').text('New users added from the dashboard receive a default password: password123');
                    $('#user_id').val('');
                }

                modal.fadeIn(180);
            }

            function closeModal() {
                modal.fadeOut(150);
            }

            $('#openAddUserModal').on('click', function () {
                openModal('add');
            });

            $('#closeUserModal, #cancelUserModal').on('click', closeModal);

            $(document).on('click', '.edit-user', function () {
                openModal('edit', $(this).data());
            });

            $('#saveUserBtn').on('click', function () {
                hideMessage(formMessage);

                if (!validateUserForm()) {
                    return;
                }

                const userId = $('#user_id').val();
                const isEdit = !!userId;
                const url = isEdit ? userActionUrlTemplate.replace('__ID__', userId) : '{{ route('users.store') }}';
                const type = isEdit ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: type,
                    data: form.serialize(),
                    success: function (response) {
                        showMessage(tableMessage, 'success', response.message);
                        closeModal();
                        usersTable.ajax.reload(null, false);
                        refreshStats();
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            showMessage(formMessage, 'error', Object.values(xhr.responseJSON.errors).flat().join(' '));
                            return;
                        }

                        showMessage(formMessage, 'error', xhr.responseJSON?.message || 'Something went wrong. Please try again.');
                    }
                });
            });

            $(document).on('click', '.delete-user', function () {
                hideMessage(tableMessage);

                if (!confirm('Are you sure you want to delete this user?')) {
                    return;
                }

                $.ajax({
                    url: userActionUrlTemplate.replace('__ID__', $(this).data('id')),
                    type: 'DELETE',
                    success: function (response) {
                        showMessage(tableMessage, 'success', response.message);
                        usersTable.ajax.reload(null, false);
                        refreshStats();
                    },
                    error: function (xhr) {
                        showMessage(tableMessage, 'error', xhr.responseJSON?.message || 'Unable to delete the user.');
                    }
                });
            });

            $(window).on('click', function (event) {
                if ($(event.target).is(modal)) {
                    closeModal();
                }
            });
        });
    </script>
@endpush
