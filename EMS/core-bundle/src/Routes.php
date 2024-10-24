<?php

declare(strict_types=1);

namespace EMS\CoreBundle;

class Routes
{
    final public const AUTH_TOKEN_LOGIN = 'emsco_auth_token_login';
    final public const EDIT_REVISION = 'emsco_edit_revision';
    final public const VIEW_REVISIONS = 'emsco_view_revisions';
    final public const VIEW_REVISIONS_AUDIT = 'emsco_view_revisions_table_audit';
    final public const DISCARD_DRAFT = 'emsco_discard_draft';
    final public const DRAFT_IN_PROGRESS = 'emsco_draft_in_progress';
    final public const DRAFT_IN_PROGRESS_AJAX = 'emsco_draft_in_progress_ajax';
    final public const DASHBOARD_ADMIN_INDEX = 'emsco_dashboard_admin_index';
    final public const DASHBOARD_ADMIN_INDEX_AJAX = 'emsco_dashboard_admin_index_ajax';
    final public const DASHBOARD_ADMIN_ADD = 'emsco_dashboard_admin_add';
    final public const DASHBOARD_ADMIN_EDIT = 'emsco_dashboard_admin_edit';
    final public const DASHBOARD_ADMIN_DELETE = 'emsco_dashboard_admin_delete';
    final public const DASHBOARD_ADMIN_SET_QUICK_SEARCH = 'emsco_dashboard_admin_set_quick_search';
    final public const DASHBOARD_ADMIN_SET_LANDING_PAGE = 'emsco_dashboard_admin_set_landing_page';
    final public const DASHBOARD = 'emsco_dashboard';
    final public const DASHBOARD_HOME = 'emsco_dashboard_home';
    final public const RELEASE_INDEX = 'emsco_release_index';
    final public const RELEASE_VIEW = 'emsco_release_view';
    final public const RELEASE_ADD = 'emsco_release_add';
    final public const RELEASE_EDIT = 'emsco_release_edit';
    final public const RELEASE_PUBLISH = 'emsco_release_publish';
    final public const RELEASE_DELETE = 'emsco_release_delete';
    final public const RELEASE_SET_STATUS = 'emsco_release_set_status';
    final public const RELEASE_ADD_REVISION = 'emsco_release_add_revision';
    final public const RELEASE_ADD_REVISIONS = 'emsco_release_add_revisions';
    final public const RELEASE_AJAX_DATA_TABLE = 'emsco_release_ajax_data_table';
    final public const RELEASE_MEMBER_REVISION_AJAX = 'emsco_release_ajax_data_table_member_revision';
    final public const RELEASE_NON_MEMBER_REVISION_AJAX = 'emsco_release_ajax_data_table_non_member_revision';
    final public const VIEW_INDEX = 'emsco_view_index';
    final public const VIEW_EDIT = 'emsco_view_edit';
    final public const VIEW_ADD = 'emsco_view_add';
    final public const VIEW_DELETE = 'emsco_view_delete';
    final public const VIEW_DUPLICATE = 'emsco_view_duplicate';
    final public const DATA_DEFAULT_VIEW = 'emsco_data_default_view';
    final public const DATA_IN_MY_CIRCLE_VIEW = 'emsco_data_in_my_circle_view';
    final public const DATA_PUBLIC_VIEW = 'emsco_data_public_view';
    final public const DATA_PRIVATE_VIEW = 'emsco_data_private_view';
    final public const DATA_ADD = 'emsco_data_add';
    final public const DATA_TRASH = 'emsco_data_trash';
    final public const DATA_PICK_A_RELEASE_AJAX_DATA_TABLE = 'emsco_data_pick_a_release_ajax_data_table';
    final public const DATA_ADD_REVISION_TO_RELEASE = 'emsco_data_add_revision_to_release';
    final public const SCHEDULE_INDEX = 'emsco_schedule_index';
    final public const SCHEDULE_ADD = 'emsco_schedule_add';
    final public const SCHEDULE_EDIT = 'emsco_schedule_edit';
    final public const SCHEDULE_DUPLICATE = 'emsco_schedule_duplicate';
    final public const SCHEDULE_DELETE = 'emsco_schedule_delete';
    final public const USER_AJAX_DATA_TABLE = 'emsco_user_datatable';
    final public const USER_INDEX = 'emsco_user_index';
    final public const USER_ADD = 'emsco_user_add';
    final public const USER_EDIT = 'emsco_user_edit';
    final public const USER_ENABLING = 'emsco_user_enabling';
    final public const USER_API_KEY = 'emsco_user_api_key';
    final public const USER_DELETE = 'emsco_user_delete';
    final public const USER_PROFILE = 'emsco_user_profile';
    final public const USER_PROFILE_EDIT = 'emsco_user_profile_edit';
    final public const USER_CHANGE_PASSWORD = 'emsco_user_change_password';
    final public const USER_LOGOUT = 'emsco_user_logout';
    final public const USER_LOGIN = 'emsco_user_login';
    final public const LOG_INDEX = 'emsco_log_index';
    final public const LOG_DELETE = 'emsco_log_delete';
    final public const LOG_VIEW = 'emsco_log_view';
}
