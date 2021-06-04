import Layout from '@/layout';

const tableRoutes = {
  path: '/table',
  component: Layout,
  redirect: '/table/complex-table',
  name: 'Complex Table',
  meta: {
    title: 'servers',
    icon: 'table',
    permissions: ['view menu servers'],
  },
  children: [
    // {
    //   path: 'drag-table',
    //   component: () => import('@/views/table/DragTable'),
    //   name: 'DragTable',
    //   meta: {
    //      title: 'dragTable',
    //      permissions: ['view menu servers'],
    //     },
    // },
    {
      path: 'servers-status',
      component: () => import('@/views/table/StatusServers'),
      name: 'ServersStatus',
      meta: {
        title: 'serversStatus',
        permissions: ['view menu servers status'],
      },
    },
    {
      path: 'create-server',
      component: () => import('@/views/table/CreateServer'),
      name: 'CreateServer',
      meta: {
        title: 'createServer',
        permissions: ['view menu servers create'],
      },
    },
    {
      path: 'server-groups',
      component: () => import('@/views/table/ServerGroups'),
      name: 'ServerGroups',
      meta: {
        title: 'serverGroups',
        permissions: ['view menu servers groups'],
      },
    },
    {
      path: 'sms',
      component: () => import('@/views/table/Sms'),
      name: 'sms',
      meta: {
        title: 'sms',
        permissions: ['view menu sms'],
      },
    },
    // {
    //   path: 'inline-edit-table',
    //   component: () => import('@/views/table/InlineEditTable'),
    //   name: 'InlineEditTable',
    //   meta: { title: 'inlineEditTable' },
    // },
    // {
    //   path: 'complex-table',
    //   component: () => import('@/views/table/ComplexTable'),
    //   name: 'ComplexTable',
    //   meta: { title: 'complexTable' },
    // },
  ],
};
export default tableRoutes;
