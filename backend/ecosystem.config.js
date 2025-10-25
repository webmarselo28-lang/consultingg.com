/**
 * PM2 Ecosystem Configuration
 * ConsultingG Real Estate Backend
 *
 * Usage:
 *   pm2 start ecosystem.config.js
 *   pm2 start ecosystem.config.js --env production
 *   pm2 reload ecosystem.config.js
 *   pm2 delete ecosystem.config.js
 */

module.exports = {
  apps: [
    {
      name: 'consultingg-backend',
      script: './dist/server.js',
      cwd: '/home/webmarselo28-lang/goro.consultingg.com/backend',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '512M',

      // Environment variables
      env: {
        NODE_ENV: 'development',
        PORT: 3000,
      },

      env_production: {
        NODE_ENV: 'production',
        PORT: 3000,
      },

      // Logging
      error_file: './logs/error.log',
      out_file: './logs/output.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,

      // Advanced settings
      min_uptime: '10s',
      max_restarts: 10,
      restart_delay: 4000,
      kill_timeout: 5000,

      // Node.js args
      node_args: '--max-old-space-size=512',

      // Monitoring
      instance_var: 'INSTANCE_ID',

      // Post-deployment
      post_update: ['npm run build', 'npx prisma generate'],

      // Health check (optional)
      // wait_ready: true,
      // listen_timeout: 10000,
    }
  ],

  /**
   * Deployment configuration (optional)
   * Uncomment to use PM2 deploy feature
   */
  /*
  deploy: {
    production: {
      user: 'webmarselo28-lang',
      host: 'your-server.com',
      ref: 'origin/main',
      repo: 'git@github.com:username/consultingg-backend.git',
      path: '/home/webmarselo28-lang/goro.consultingg.com/backend',
      'post-deploy': 'npm ci --production && npm run build && pm2 reload ecosystem.config.js --env production && pm2 save'
    }
  }
  */
};
