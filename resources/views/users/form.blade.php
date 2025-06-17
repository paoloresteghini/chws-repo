<table class="kt-table align-start text-sm text-muted-foreground">
    <tr>
        <td class="text-secondary-foreground font-normal">
            Name
        </td>
        <td class="text-foreground font-normal">
            <div class="kt-input max-w-[400px]">
                <input class="input" id="name" type="text" name="name"
                       value="{{ old('name', $user->name ?? '') }}" required>
            </div>
        </td>
    </tr>
    <tr>
        <td class="text-secondary-foreground font-normal">
            Email
        </td>
        <td class="text-foreground font-normal">
            <div class="kt-input max-w-[400px]">
                <input class="input" id="email" type="email" name="email"
                       value="{{ old('email', $user->email ?? '') }}" required>
            </div>
        </td>
    </tr>
    @if(Route::currentRouteName() === 'users.create')
        <tr>
            <td class="text-secondary-foreground font-normal">
                Password
            </td>
            <td class="text-foreground font-normal">
                <div class="kt-input max-w-[400px]">
                    <input class="input" id="password" type="password" name="password"
                           value="{{ old('password') }}" required>
                </div>
            </td>
        </tr>
        <tr>
            <td class="text-secondary-foreground font-normal">
                Confirm Password
            </td>
            <td class="text-foreground font-normal">
                <div class="kt-input max-w-[400px]">
                    <input class="input" id="password_confirmation" type="password" name="password_confirmation"
                           value="{{ old('password_confirmation') }}" required>
                </div>
            </td>
        </tr>
    @elseif(Route::currentRouteName() === 'users.edit')
        <tr>
            <td class="text-secondary-foreground font-normal">
                New Password
            </td>
            <td class="text-foreground font-normal">
                <div class="kt-input max-w-[400px]">
                    <input class="input" id="password" type="password" name="password"
                           placeholder="Leave blank to keep current password">
                </div>
            </td>
        </tr>
        <tr>
            <td class="text-secondary-foreground font-normal">
                Confirm New Password
            </td>
            <td class="text-foreground font-normal">
                <div class="kt-input max-w-[400px]">
                    <input class="input" id="password_confirmation" type="password" name="password_confirmation"
                           placeholder="Leave blank to keep current password">
                </div>
            </td>
        </tr>
    @endif
</table>