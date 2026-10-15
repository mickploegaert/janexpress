-- Accounts horen in public.users.
-- users.id moet een bestaande Auth-gebruiker zijn, daarom kopiëren we customers niet.
-- Voer dit script uit in de Supabase SQL Editor.

alter table public.users add column if not exists password_hash text;

create unique index if not exists users_email_lower_unique on public.users (lower(email));

grant select, insert, update on table public.users to service_role;
grant select, insert, update, delete on table public.products to service_role;
grant select, insert, update, delete on table public.orders to service_role;
grant select, insert, update, delete on table public.order_items to service_role;
grant usage, select on all sequences in schema public to service_role;

notify pgrst, 'reload schema';
